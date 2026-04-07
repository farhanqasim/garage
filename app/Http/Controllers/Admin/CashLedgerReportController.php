<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBranchAccess;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Services\CashLedgerReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashLedgerReportController extends Controller
{
    use HasBranchAccess;

    public function __construct(
        protected CashLedgerReportService $ledgerService
    ) {
        $this->middleware(function ($request, $next) {
            $u = auth()->user();
            if (! $u || (! $u->can('view_bank_transactions') && ! $u->can('view_cash_accounts') && ! $u->can('view_bank_accounts'))) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $filters = $this->buildFilters($request);
        $cashMethodId = $this->ledgerService->cashMethodId();

        $opening = $this->ledgerService->openingBalance($from->copy()->startOfDay(), $filters, $cashMethodId);
        $rows = $this->ledgerService->collectRows($from, $to, $filters, $cashMethodId);
        $summary = $this->ledgerService->summary($rows, $opening);

        $page = max(1, (int) $request->get('page', 1));
        $perPage = max(10, min(200, (int) $request->get('per_page', 25)));

        $paginator = $this->ledgerService->paginate($rows, $opening, $page, $perPage);

        $branches = \App\Models\Branch::where('status', 'active')->orderBy('branch_name')->get();
        $users = User::orderBy('name')->get(['id', 'name']);

        $typeLabels = CashLedgerReportService::transactionTypeLabels();

        $preset = $request->get('preset', 'this_month');
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $preset = 'custom';
        }

        return view('admin.reports.cash-ledger', [
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $paginator,
            'branches' => $branches,
            'users' => $users,
            'typeLabels' => $typeLabels,
            'preset' => $preset,
        ]);
    }

    public function rowDetail(Request $request, string $source, int $id)
    {
        $payload = match ($source) {
            'payment' => $this->detailPayment($id),
            'bank_transaction' => $this->detailBankTransaction($id),
            'cash_transaction' => $this->detailCashTransaction($id),
            default => null,
        };

        if (! $payload) {
            return response()->json(['ok' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['ok' => true, 'data' => $payload]);
    }

    public function exportPdf(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $filters = $this->buildFilters($request);
        $cashMethodId = $this->ledgerService->cashMethodId();

        $opening = $this->ledgerService->openingBalance($from->copy()->startOfDay(), $filters, $cashMethodId);
        $rows = $this->ledgerService->collectRows($from, $to, $filters, $cashMethodId);
        $summary = $this->ledgerService->summary($rows, $opening);

        $running = $opening;
        $withBalance = $rows->map(function (array $row) use (&$running) {
            $net = (float) ($row['debit'] ?? 0) - (float) ($row['credit'] ?? 0);
            $running += $net;
            $row['running_balance'] = round($running, 2);

            return $row;
        });

        $pdf = Pdf::loadView('admin.reports.cash-ledger-pdf', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'rows' => $withBalance,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        $fn = 'cash-ledger-'.$from->format('Y-m-d').'_'.$to->format('Y-m-d').'.pdf';

        return $pdf->download($fn);
    }

    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $filters = $this->buildFilters($request);
        $cashMethodId = $this->ledgerService->cashMethodId();

        $opening = $this->ledgerService->openingBalance($from->copy()->startOfDay(), $filters, $cashMethodId);
        $rows = $this->ledgerService->collectRows($from, $to, $filters, $cashMethodId);

        $running = $opening;
        $lines = [];
        foreach ($rows as $row) {
            $net = (float) ($row['debit'] ?? 0) - (float) ($row['credit'] ?? 0);
            $running += $net;
            $lines[] = [
                $row['date'] ?? '',
                $row['time'] ?? '',
                $row['voucher_ref'] ?? '',
                $row['transaction_type_label'] ?? '',
                $row['description'] ?? '',
                $row['party'] ?? '',
                $row['created_by'] ?? '',
                number_format((float) ($row['debit'] ?? 0), 2, '.', ''),
                number_format((float) ($row['credit'] ?? 0), 2, '.', ''),
                number_format($running, 2, '.', ''),
                $row['branch'] ?? '',
                $row['source'].'/'.$row['source_id'],
            ];
        }

        $filename = 'cash-ledger-'.$from->format('Y-m-d').'_'.$to->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($lines) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Date', 'Time', 'Voucher / Ref', 'Type', 'Description', 'Party', 'Created by',
                'Debit', 'Credit', 'Running balance', 'Branch', 'Source',
            ]);
            foreach ($lines as $line) {
                fputcsv($out, $line);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function thermalPrint(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $filters = $this->buildFilters($request);
        $cashMethodId = $this->ledgerService->cashMethodId();

        $opening = $this->ledgerService->openingBalance($from->copy()->startOfDay(), $filters, $cashMethodId);
        $rows = $this->ledgerService->collectRows($from, $to, $filters, $cashMethodId);
        $summary = $this->ledgerService->summary($rows, $opening);

        $running = $opening;
        $withBalance = $rows->map(function (array $row) use (&$running) {
            $net = (float) ($row['debit'] ?? 0) - (float) ($row['credit'] ?? 0);
            $running += $net;
            $row['running_balance'] = round($running, 2);

            return $row;
        });

        $paper = (string) $request->get('paper', '80');
        if (! in_array($paper, ['58', '80'], true)) {
            $paper = '80';
        }

        $branchName = 'All Branches';
        if (! empty($filters['branch_id'])) {
            $branchName = \App\Models\Branch::where('id', (int) $filters['branch_id'])->value('branch_name') ?: 'All Branches';
        } else {
            $branchInfo = $this->getBranchInfoForDisplay(Auth::user());
            $branchName = $branchInfo['name'] ?? 'All Branches';
        }

        return view('admin.reports.cash-ledger-thermal', [
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $withBalance,
            'paper' => $paper,
            'branchName' => $branchName,
            'generatedAt' => now(),
            'userName' => Auth::user()?->name ?? 'User',
            'typeLabels' => CashLedgerReportService::transactionTypeLabels(),
        ]);
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->get('preset', 'this_month');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $to = Carbon::parse($request->date_to)->endOfDay();

            return [$from, $to];
        }

        return match ($preset) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    /**
     * @return array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null, party?: string|null}
     */
    protected function buildFilters(Request $request): array
    {
        $filters = [
            'branch_id' => $request->filled('branch_id') ? (int) $request->branch_id : null,
            'user_id' => $request->filled('user_id') ? (int) $request->user_id : null,
            'type' => $request->filled('type') ? (string) $request->type : null,
            'q' => $request->filled('q') ? trim((string) $request->q) : null,
            'party' => $request->filled('party') ? trim((string) $request->party) : null,
        ];

        $user = Auth::user();
        if ($user && $user->role !== 'admin') {
            $bid = $this->getUserBranchId($user);
            if ($bid && empty($filters['branch_id'])) {
                $filters['branch_id'] = $bid;
            }
        }

        return $filters;
    }

    protected function detailPayment(int $id): ?array
    {
        $p = Payment::with(['user', 'customer', 'supplier', 'paymentMethod', 'sales.branch', 'purchases.branch', 'bankAccount'])->find($id);
        if (! $p) {
            return null;
        }

        $sale = $p->sales->first();
        $purchase = $p->purchases->first();

        return [
            'title' => 'Payment #'.$p->id,
            'meta' => [
                'Amount' => number_format((float) $p->amount, 2).' PKR',
                'Direction' => $p->direction,
                'Method' => $p->paymentMethod?->name ?? '—',
                'Status' => $p->status,
                'Date' => $p->payment_date?->format('Y-m-d') ?? '—',
            ],
            'links' => array_values(array_filter([
                ['label' => 'Open payment', 'url' => route('admin.payments.show', $p->id)],
                $sale ? ['label' => 'Sale invoice', 'url' => route('sales.show', $sale->id)] : null,
                $purchase ? ['label' => 'Purchase bill', 'url' => route('purchases.show', $purchase->id)] : null,
            ])),
            'notes' => $p->notes,
        ];
    }

    protected function detailBankTransaction(int $id): ?array
    {
        $t = BankTransaction::with(['bankAccount', 'matchedPayment'])->find($id);
        if (! $t) {
            return null;
        }

        return [
            'title' => 'Cash account transaction #'.$t->id,
            'meta' => [
                'Amount' => number_format((float) $t->amount, 2).' PKR',
                'Type' => $t->type,
                'Date' => $t->transaction_date?->format('Y-m-d') ?? '—',
                'Reference' => $t->statement_reference ?? '—',
            ],
            'links' => [
                ['label' => 'Open transaction', 'url' => route('admin.bank-transactions.show', $t->id)],
            ],
            'notes' => $t->description,
        ];
    }

    protected function detailCashTransaction(int $id): ?array
    {
        $t = CashTransaction::with(['user', 'relatedUser', 'branch'])->find($id);
        if (! $t) {
            return null;
        }

        return [
            'title' => 'Wallet transaction #'.$t->id,
            'meta' => [
                'Amount' => number_format((float) $t->amount, 2).' PKR',
                'Direction' => $t->direction,
                'Type' => $t->type,
                'Created' => $t->created_at?->format('Y-m-d H:i') ?? '—',
            ],
            'links' => [
                ['label' => 'Cash transactions list', 'url' => route('admin.cash-transactions.index')],
            ],
            'notes' => $t->note,
        ];
    }
}
