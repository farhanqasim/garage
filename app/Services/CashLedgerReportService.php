<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CashLedgerReportService
{
    public const TYPE_SALE_CASH = 'sale_cash';

    public const TYPE_PURCHASE_CASH = 'purchase_cash';

    public const TYPE_CASH_PAYMENT = 'cash_payment';

    public const TYPE_CASH_ACCOUNT_IN = 'cash_account_in';

    public const TYPE_CASH_ACCOUNT_OUT = 'cash_account_out';

    public const TYPE_WALLET = 'wallet';

    /**
     * @return array<string, string>
     */
    public static function transactionTypeLabels(): array
    {
        return [
            self::TYPE_SALE_CASH => 'Sale (Cash)',
            self::TYPE_PURCHASE_CASH => 'Purchase (Cash)',
            self::TYPE_CASH_PAYMENT => 'Cash Payment',
            self::TYPE_CASH_ACCOUNT_IN => 'Cash Account — In',
            self::TYPE_CASH_ACCOUNT_OUT => 'Cash Account — Out',
            self::TYPE_WALLET => 'Wallet / Internal',
        ];
    }

    public function cashMethodId(): ?int
    {
        return PaymentMethod::where('code', 'cash')->value('id');
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    public function openingBalance(Carbon $beforeStart, array $filters, ?int $cashMethodId): float
    {
        if (! $cashMethodId) {
            return 0.0;
        }

        $net = 0.0;

        $net += $this->paymentsNetBefore($beforeStart, $filters, $cashMethodId);
        $net += $this->bankTransactionsNetBefore($beforeStart, $filters);
        $net += $this->cashTransactionsNetBefore($beforeStart, $filters);

        return round($net, 2);
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    public function collectRows(Carbon $from, Carbon $to, array $filters, ?int $cashMethodId): Collection
    {
        $rows = collect();

        if ($cashMethodId && $this->shouldIncludeType($filters, [
            self::TYPE_SALE_CASH,
            self::TYPE_PURCHASE_CASH,
            self::TYPE_CASH_PAYMENT,
        ])) {
            $rows = $rows->merge($this->paymentRows($from, $to, $filters, $cashMethodId));
        }

        if ($this->shouldIncludeType($filters, [
            self::TYPE_CASH_ACCOUNT_IN,
            self::TYPE_CASH_ACCOUNT_OUT,
        ])) {
            $rows = $rows->merge($this->bankTransactionRows($from, $to, $filters));
        }

        if ($this->shouldIncludeType($filters, [self::TYPE_WALLET])) {
            $rows = $rows->merge($this->walletRows($from, $to, $filters));
        }

        return $rows->sortBy('sort_ts')->values();
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    public function paginate(Collection $sortedRows, float $opening, int $page = 1, int $perPage = 25): LengthAwarePaginator
    {
        $perPage = max(10, min(200, $perPage));
        $page = max(1, $page);

        $running = $opening;
        $withBalance = $sortedRows->map(function (array $row) use (&$running) {
            $net = (float) ($row['debit'] ?? 0) - (float) ($row['credit'] ?? 0);
            $running += $net;
            $row['running_balance'] = round($running, 2);

            return $row;
        });

        $total = $withBalance->count();
        $slice = $withBalance->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page', 'query' => request()->query()]
        );
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    public function summary(Collection $sortedRows, float $opening): array
    {
        $totalIn = round((float) $sortedRows->sum('debit'), 2);
        $totalOut = round((float) $sortedRows->sum('credit'), 2);
        $net = round($totalIn - $totalOut, 2);
        $closing = round($opening + $net, 2);

        return [
            'opening_balance' => round($opening, 2),
            'total_cash_in' => $totalIn,
            'total_cash_out' => $totalOut,
            'net_cash_flow' => $net,
            'closing_balance' => $closing,
        ];
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function shouldIncludeType(array $filters, array $types): bool
    {
        $t = $filters['type'] ?? null;
        if (! $t || $t === '') {
            return true;
        }

        return in_array($t, $types, true);
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function paymentsNetBefore(Carbon $beforeStart, array $filters, int $cashMethodId): float
    {
        $q = Payment::query()
            ->where('payment_method_id', $cashMethodId)
            ->where('status', 'paid')
            ->whereDate('payment_date', '<', $beforeStart->toDateString());

        $this->applyPaymentFilters($q, $filters, $cashMethodId);

        $in = (clone $q)->where('direction', 'in')->sum('amount');
        $out = (clone $q)->where('direction', 'out')->sum('amount');

        return (float) $in - (float) $out;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function bankTransactionsNetBefore(Carbon $beforeStart, array $filters): float
    {
        $q = BankTransaction::query()
            ->whereHas('bankAccount', fn (Builder $b) => $b->where('account_type', 'cash'))
            ->whereDate('transaction_date', '<', $beforeStart->toDateString());

        $this->applyBankTxFilters($q, $filters);

        $credit = (clone $q)->where('type', 'credit')->sum('amount');
        $debit = (clone $q)->where('type', 'debit')->sum('amount');

        return (float) $credit - (float) $debit;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function cashTransactionsNetBefore(Carbon $beforeStart, array $filters): float
    {
        $q = CashTransaction::query()
            ->where('created_at', '<', $beforeStart);

        $this->applyCashTxFilters($q, $filters);

        $in = (clone $q)->where('direction', 'credit')->sum('amount');
        $out = (clone $q)->where('direction', 'debit')->sum('amount');

        return (float) $in - (float) $out;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function paymentRows(Carbon $from, Carbon $to, array $filters, int $cashMethodId): Collection
    {
        $q = Payment::query()
            ->with([
                'user',
                'customer',
                'supplier',
                'paymentMethod',
                'sales' => fn ($r) => $r->with('branch'),
                'purchases' => fn ($r) => $r->with('branch'),
                'bankAccount.branch',
            ])
            ->where('payment_method_id', $cashMethodId)
            ->where('status', 'paid')
            ->whereDate('payment_date', '>=', $from->toDateString())
            ->whereDate('payment_date', '<=', $to->toDateString());

        $this->applyPaymentFilters($q, $filters, $cashMethodId);

        $out = collect();
        foreach ($q->orderBy('payment_date')->orderBy('id')->cursor() as $payment) {
            $branchId = $this->resolvePaymentBranchId($payment);
            $branchName = $this->branchName($branchId);

            $sale = $payment->sales->first();
            $purchase = $payment->purchases->first();

            $typeLabel = self::TYPE_CASH_PAYMENT;
            if ($sale) {
                $typeLabel = self::TYPE_SALE_CASH;
            } elseif ($purchase) {
                $typeLabel = self::TYPE_PURCHASE_CASH;
            }

            if ($filters['type'] ?? null) {
                if (($filters['type'] ?? '') !== $typeLabel) {
                    continue;
                }
            }

            $party = $this->partyName($payment);
            $ref = $this->paymentReference($payment, $sale, $purchase);
            $desc = $payment->notes ?? '';

            $dt = $this->paymentDateTime($payment);
            $debit = $payment->direction === 'in' ? (float) $payment->amount : 0.0;
            $credit = $payment->direction === 'out' ? (float) $payment->amount : 0.0;

            $out->push([
                'sort_ts' => $dt->timestamp,
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s'),
                'voucher_ref' => $ref,
                'transaction_type' => $typeLabel,
                'transaction_type_label' => self::transactionTypeLabels()[$typeLabel] ?? $typeLabel,
                'description' => $desc,
                'party' => $party,
                'created_by' => $payment->user?->name ?? '—',
                'debit' => $debit,
                'credit' => $credit,
                'branch' => $branchName,
                'branch_id' => $branchId,
                'source' => 'payment',
                'source_id' => $payment->id,
                'links' => $this->paymentLinks($payment, $sale, $purchase),
            ]);
        }

        return $out;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function bankTransactionRows(Carbon $from, Carbon $to, array $filters): Collection
    {
        $q = BankTransaction::query()
            ->with(['bankAccount.branch', 'matchedPayment.user'])
            ->whereHas('bankAccount', fn (Builder $b) => $b->where('account_type', 'cash'))
            ->whereDate('transaction_date', '>=', $from->toDateString())
            ->whereDate('transaction_date', '<=', $to->toDateString());

        $this->applyBankTxFilters($q, $filters);

        $out = collect();
        foreach ($q->orderBy('transaction_date')->orderBy('id')->cursor() as $tx) {
            $typeLabel = $tx->type === 'credit' ? self::TYPE_CASH_ACCOUNT_IN : self::TYPE_CASH_ACCOUNT_OUT;

            if ($filters['type'] ?? null) {
                if (($filters['type'] ?? '') !== $typeLabel) {
                    continue;
                }
            }

            $branchId = $tx->bankAccount?->branch_id;
            $branchName = $this->branchName($branchId);
            $dt = Carbon::parse($tx->transaction_date->format('Y-m-d').' '.($tx->created_at?->format('H:i:s') ?? '00:00:00'));

            $debit = $tx->type === 'credit' ? (float) $tx->amount : 0.0;
            $credit = $tx->type === 'debit' ? (float) $tx->amount : 0.0;

            $out->push([
                'sort_ts' => $dt->timestamp,
                'date' => $tx->transaction_date->format('Y-m-d'),
                'time' => $tx->created_at?->format('H:i:s') ?? '00:00:00',
                'voucher_ref' => $tx->statement_reference ?: 'BT-'.$tx->id,
                'transaction_type' => $typeLabel,
                'transaction_type_label' => self::transactionTypeLabels()[$typeLabel] ?? $typeLabel,
                'description' => $tx->description ?? '',
                'party' => $tx->bankAccount?->account_title ?? 'Cash Account',
                'created_by' => $tx->matchedPayment?->user?->name ?? '—',
                'debit' => $debit,
                'credit' => $credit,
                'branch' => $branchName,
                'branch_id' => $branchId,
                'source' => 'bank_transaction',
                'source_id' => $tx->id,
                'links' => [
                    ['label' => 'Bank transaction', 'url' => route('admin.bank-transactions.show', $tx->id)],
                ],
            ]);
        }

        return $out;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function walletRows(Carbon $from, Carbon $to, array $filters): Collection
    {
        $q = CashTransaction::query()
            ->with(['user', 'relatedUser', 'branch'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString());

        $this->applyCashTxFilters($q, $filters);

        $out = collect();
        foreach ($q->orderBy('created_at')->orderBy('id')->cursor() as $tx) {
            $typeLabel = self::TYPE_WALLET;

            $dt = $tx->created_at instanceof Carbon ? $tx->created_at : Carbon::parse($tx->created_at);
            $debit = $tx->direction === 'credit' ? (float) $tx->amount : 0.0;
            $credit = $tx->direction === 'debit' ? (float) $tx->amount : 0.0;

            $party = $tx->relatedUser?->name ?? $tx->user?->name ?? '—';
            $label = $this->walletTypeLabel($tx->type);

            $out->push([
                'sort_ts' => $dt->timestamp,
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s'),
                'voucher_ref' => 'CW-'.$tx->id,
                'transaction_type' => $typeLabel,
                'transaction_type_label' => $label,
                'description' => $tx->note ?? $label,
                'party' => $party,
                'created_by' => $tx->user?->name ?? '—',
                'debit' => $debit,
                'credit' => $credit,
                'branch' => $tx->branch?->branch_name ?? '—',
                'branch_id' => $tx->branch_id,
                'source' => 'cash_transaction',
                'source_id' => $tx->id,
                'links' => [
                    ['label' => 'Cash transactions list', 'url' => route('admin.cash-transactions.index')],
                ],
            ]);
        }

        return $out;
    }

    protected function walletTypeLabel(string $type): string
    {
        return match ($type) {
            'job_payment' => 'Wallet — Job payment',
            'cash_transfer' => 'Wallet — Cash transfer',
            'bank_transfer' => 'Wallet — Bank transfer',
            'commission' => 'Wallet — Commission',
            'admin_adjustment' => 'Wallet — Adjustment',
            'shop_expense' => 'Wallet — Shop expense',
            default => 'Wallet — '.$type,
        };
    }

    protected function paymentDateTime(Payment $payment): Carbon
    {
        $date = $payment->payment_date instanceof Carbon
            ? $payment->payment_date->format('Y-m-d')
            : Carbon::parse($payment->payment_date)->format('Y-m-d');

        $time = $payment->paid_at
            ? Carbon::parse($payment->paid_at)->format('H:i:s')
            : Carbon::parse($payment->created_at)->format('H:i:s');

        return Carbon::parse($date.' '.$time);
    }

    protected function resolvePaymentBranchId(Payment $payment): ?int
    {
        $sale = $payment->sales->first();
        if ($sale && $sale->branch_id) {
            return (int) $sale->branch_id;
        }
        $purchase = $payment->purchases->first();
        if ($purchase && $purchase->branch_id) {
            return (int) $purchase->branch_id;
        }
        if ($payment->bankAccount && $payment->bankAccount->branch_id) {
            return (int) $payment->bankAccount->branch_id;
        }

        return null;
    }

    protected function branchName(?int $branchId): string
    {
        if (! $branchId) {
            return '—';
        }
        static $cache = [];
        if (! isset($cache[$branchId])) {
            $cache[$branchId] = \App\Models\Branch::where('id', $branchId)->value('branch_name') ?? '—';
        }

        return $cache[$branchId];
    }

    protected function partyName(Payment $payment): string
    {
        if ($payment->customer_id && $payment->customer) {
            $names = $payment->customer->names;

            return is_array($names) && isset($names[0]) ? (string) $names[0] : 'Customer #'.$payment->customer_id;
        }
        if ($payment->supplier_id && $payment->supplier) {
            $names = $payment->supplier->names;

            return is_array($names) && isset($names[0]) ? (string) $names[0] : 'Supplier #'.$payment->supplier_id;
        }

        return '—';
    }

    protected function paymentReference(Payment $payment, $sale, $purchase): string
    {
        if ($payment->transaction_id) {
            return (string) $payment->transaction_id;
        }
        if ($sale) {
            return 'sale:'.($sale->reference ?: $sale->id);
        }
        if ($purchase) {
            return (string) ($purchase->invoice_no ?? 'pur:'.$purchase->id);
        }

        return 'PAY-'.$payment->id;
    }

    protected function paymentLinks(Payment $payment, $sale, $purchase): array
    {
        $links = [
            ['label' => 'Payment', 'url' => route('admin.payments.show', $payment->id)],
        ];
        if ($sale) {
            $links[] = ['label' => 'Sale invoice', 'url' => route('sales.show', $sale->id)];
        }
        if ($purchase) {
            $links[] = ['label' => 'Purchase', 'url' => route('purchases.show', $purchase->id)];
        }

        return $links;
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function applyPaymentFilters(Builder $q, array $filters, int $cashMethodId): void
    {
        if (! empty($filters['user_id'])) {
            $q->where('user_id', (int) $filters['user_id']);
        }

        $branchId = $filters['branch_id'] ?? null;
        if ($branchId) {
            $bid = (int) $branchId;
            $q->where(function (Builder $b) use ($bid) {
                $b->whereHas('sales', fn (Builder $s) => $s->where('branch_id', $bid))
                    ->orWhereHas('purchases', fn (Builder $p) => $p->where('branch_id', $bid))
                    ->orWhereHas('bankAccount', fn (Builder $a) => $a->where('branch_id', $bid));
            });
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $q->where(function (Builder $b) use ($like, $search) {
                $b->where('transaction_id', 'like', $like)
                    ->orWhere('notes', 'like', $like)
                    ->orWhere('id', $search)
                    ->orWhereHas('sales', fn (Builder $s) => $s->where('reference', 'like', $like))
                    ->orWhereHas('purchases', fn (Builder $p) => $p->where('invoice_no', 'like', $like));
            });
        }

        $party = trim((string) ($filters['party'] ?? ''));
        if ($party !== '') {
            $plike = '%'.$party.'%';
            $q->where(function (Builder $b) use ($plike) {
                $b->whereHas('customer', fn (Builder $c) => $c->where('names', 'like', $plike))
                    ->orWhereHas('supplier', fn (Builder $s) => $s->where('names', 'like', $plike));
            });
        }
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function applyBankTxFilters(Builder $q, array $filters): void
    {
        if (! empty($filters['branch_id'])) {
            $q->whereHas('bankAccount', fn (Builder $b) => $b->where('branch_id', (int) $filters['branch_id']));
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $q->where(function (Builder $b) use ($like, $search) {
                $b->where('statement_reference', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('id', $search);
            });
        }

        $party = trim((string) ($filters['party'] ?? ''));
        if ($party !== '') {
            $plike = '%'.$party.'%';
            $q->whereHas('bankAccount', fn (Builder $b) => $b->where('account_title', 'like', $plike));
        }
    }

    /**
     * @param  array{branch_id?: int|null, user_id?: int|null, type?: string|null, q?: string|null}  $filters
     */
    protected function applyCashTxFilters(Builder $q, array $filters): void
    {
        if (! empty($filters['branch_id'])) {
            $q->where('branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['user_id'])) {
            $q->where('user_id', (int) $filters['user_id']);
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $q->where(function (Builder $b) use ($like, $search) {
                $b->where('note', 'like', $like)
                    ->orWhere('id', $search);
            });
        }

        $party = trim((string) ($filters['party'] ?? ''));
        if ($party !== '') {
            $plike = '%'.$party.'%';
            $q->where(function (Builder $b) use ($plike) {
                $b->whereHas('user', fn (Builder $u) => $u->where('name', 'like', $plike))
                    ->orWhereHas('relatedUser', fn (Builder $u) => $u->where('name', 'like', $plike));
            });
        }
    }
}
