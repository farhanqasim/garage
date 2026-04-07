<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use App\Models\Branch;
use App\Models\CarWashAttendance;
use App\Models\CarWashJob;
use App\Models\CarWashPayment;
use App\Models\CarWashShopExpense;
use App\Models\CashTransaction;
use App\Models\CashTransfer;
use App\Models\Payment;
use App\Models\PurchaseCart;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WorkerCashTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function all_users(Request $request)
    {
        // Status filter: default Active (sirf active users), optional Inactive ya All
        $statusFilter = $request->get('status', 'active');
        if (! in_array($statusFilter, ['active', 'inactive', 'all'], true)) {
            $statusFilter = 'active';
        }

        $query = User::query()
            ->whereIn('role', ['user', 'manager', 'salesman', 'purchaser', 'employee', 'worker'])
            ->with(['assignedBranches', 'roles'])
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->select('users.*');

        if ($statusFilter === 'active') {
            $query->where('users.status', 'active');
        } elseif ($statusFilter === 'inactive') {
            $query->where('users.status', 'inactive');
        }
        // 'all' = no status filter

        $users = $query
            ->orderByRaw("CASE WHEN users.status = 'active' THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN users.branch_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('branches.id')
            ->orderByRaw("CASE users.role WHEN 'manager' THEN 1 WHEN 'employee' THEN 2 WHEN 'worker' THEN 3 WHEN 'salesman' THEN 4 WHEN 'purchaser' THEN 5 WHEN 'user' THEN 6 ELSE 7 END")
            ->paginate(10)
            ->withQueryString();

        $branches = Branch::orderBy('branch_name', 'asc')->get();
        $spatieRoles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'branches', 'spatieRoles', 'statusFilter'));
    }

    public function deleteuser($id)
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Agar user se koi transaction hai to delete na karein, sirf warn karein aur deactivate karein
        if ($user->hasTransactionHistory()) {
            $user->status = 'inactive';
            $user->save();

            return redirect()->back()->with('warning', 'Is user se linked transactions hain, is liye delete nahi kiya gaya. User ko deactivate kar diya gaya hai.');
        }

        // ✅ Delete profile image if exists
        if (! empty($user->profile_img) && file_exists(public_path($user->profile_img))) {
            unlink(public_path($user->profile_img));
        }

        // ✅ Delete user record
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * User ki tamam transactions ki report - list ki shakal mein: tareekh, raqam, qisam, mutaliqa item.
     */
    public function transactionsReport($id)
    {
        $user = User::findOrFail($id);
        $rows = [];

        // Sales
        foreach (Sale::where('user_id', $user->id)->with(['saleItems.item'])->orderBy('sale_date', 'desc')->get() as $s) {
            $items = $s->saleItems->map(fn ($i) => ($i->item->name ?? 'Item').' × '.$i->quantity)->take(3)->implode(', ');
            if ($s->saleItems->count() > 3) {
                $items .= ' +'.($s->saleItems->count() - 3).' more';
            }
            $rows[] = [
                'date' => $s->sale_date ? Carbon::parse($s->sale_date) : $s->created_at,
                'ref' => 'Sale #'.$s->id.($s->reference ? ' ('.$s->reference.')' : ''),
                'type' => 'Sale',
                'detail' => $items ?: 'Sale',
                'amount' => $s->grand_total,
            ];
        }

        // Payments (vyapar)
        foreach (Payment::where('user_id', $user->id)->with(['customer', 'supplier'])->orderBy('payment_date', 'desc')->get() as $p) {
            $custName = $p->customer ? (is_array($p->customer->names ?? null) ? ($p->customer->names[0] ?? 'N/A') : 'N/A') : null;
            $detail = $p->customer ? 'Customer: '.$custName : ($p->supplier ? 'Supplier' : 'Payment');
            $rows[] = [
                'date' => $p->payment_date ? Carbon::parse($p->payment_date) : $p->created_at,
                'ref' => $p->transaction_id ?: ('Payment #'.$p->id),
                'type' => 'Payment',
                'detail' => $detail.' – '.($p->direction ?? ''),
                'amount' => $p->amount,
            ];
        }

        // Purchase cart
        foreach (PurchaseCart::where('user_id', $user->id)->with('item')->orderBy('created_at', 'desc')->get() as $pc) {
            $rows[] = [
                'date' => $pc->created_at,
                'ref' => 'Cart #'.$pc->id,
                'type' => 'Purchase Cart',
                'detail' => $pc->item_name ?? ($pc->item->name ?? 'Item').' × '.$pc->quantity,
                'amount' => $pc->total ?? null,
            ];
        }

        // Cash transactions
        foreach (CashTransaction::where('user_id', $user->id)->orWhere('related_user_id', $user->id)->orderBy('created_at', 'desc')->get() as $ct) {
            $rows[] = [
                'date' => $ct->created_at,
                'ref' => 'CT #'.$ct->id,
                'type' => 'Cash '.($ct->type ?? $ct->direction),
                'detail' => $ct->note ?: $ct->reference_table.' #'.$ct->reference_id,
                'amount' => $ct->amount,
            ];
        }

        // Cash transfers (from or to user)
        foreach (CashTransfer::where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)->with(['fromUser', 'toUser'])->orderBy('created_at', 'desc')->get() as $cf) {
            $dir = $cf->from_user_id == $user->id ? 'Out' : 'In';
            $other = $cf->from_user_id == $user->id ? ($cf->toUser->name ?? '') : ($cf->fromUser->name ?? '');
            $rows[] = [
                'date' => $cf->created_at,
                'ref' => 'Transfer #'.$cf->id,
                'type' => 'Cash Transfer ('.$dir.')',
                'detail' => ($cf->note ?: 'To: '.$other),
                'amount' => $cf->amount,
            ];
        }

        // Car wash jobs (user as creator or worker)
        foreach (CarWashJob::where('user_id', $user->id)->orWhere('worker_user_id', $user->id)->with('customer')->orderBy('start_time', 'desc')->get() as $j) {
            $role = $j->user_id == $user->id ? 'Created' : 'Worker';
            $rows[] = [
                'date' => $j->start_time ?? $j->created_at,
                'ref' => 'Job #'.$j->id,
                'type' => 'Car Wash ('.$role.')',
                'detail' => $j->service_name.' – '.($j->customer_name ?? 'N/A'),
                'amount' => $j->price,
            ];
        }

        // Car wash payments (created_by or worker_user_id)
        foreach (CarWashPayment::where('created_by', $user->id)->orWhere('worker_user_id', $user->id)->with('job')->orderBy('payment_date', 'desc')->get() as $cp) {
            $rows[] = [
                'date' => $cp->payment_date ? Carbon::parse($cp->payment_date) : $cp->created_at,
                'ref' => 'CWP #'.$cp->id.($cp->transaction_id ? ' ('.$cp->transaction_id.')' : ''),
                'type' => 'Car Wash Payment ('.($cp->payment_type ?? '').')',
                'detail' => $cp->job ? 'Job #'.$cp->job->id.' – '.($cp->job->service_name ?? '') : ($cp->notes ?? ''),
                'amount' => $cp->amount,
            ];
        }

        // Worker cash transactions
        foreach (WorkerCashTransaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->get() as $wct) {
            $rows[] = [
                'date' => $wct->created_at,
                'ref' => 'WCT #'.$wct->id,
                'type' => 'Worker Cash ('.($wct->type ?? '').')',
                'detail' => $wct->note ?: ($wct->reference_type.' #'.$wct->reference_id),
                'amount' => $wct->amount,
            ];
        }

        // Bank transfers
        foreach (BankTransfer::where('user_id', $user->id)->orderBy('requested_at', 'desc')->get() as $bt) {
            $rows[] = [
                'date' => $bt->requested_at ?? $bt->created_at,
                'ref' => 'BT #'.$bt->id,
                'type' => 'Bank Transfer',
                'detail' => $bt->bank_name.' – '.$bt->account_title.' ('.$bt->account_number.')',
                'amount' => $bt->amount,
            ];
        }

        // Car wash attendances
        foreach (CarWashAttendance::where('user_id', $user->id)->orWhere('attended_user_id', $user->id)->orderBy('created_at', 'desc')->get() as $a) {
            $rows[] = [
                'date' => $a->created_at,
                'ref' => 'Att #'.$a->id,
                'type' => 'Attendance',
                'detail' => $a->attended_user_id == $user->id ? 'Marked attendance' : 'Marked by user',
                'amount' => null,
            ];
        }

        // Suppliers created
        foreach (Supplier::where('created_by', $user->id)->orderBy('created_at', 'desc')->get() as $s) {
            $rows[] = [
                'date' => $s->created_at,
                'ref' => 'Supplier #'.$s->id,
                'type' => 'Supplier Created',
                'detail' => $s->name ?? $s->company_name ?? 'Supplier',
                'amount' => null,
            ];
        }

        // Car wash shop expenses
        foreach (CarWashShopExpense::where('user_id', $user->id)->orderBy('expense_date', 'desc')->get() as $e) {
            $rows[] = [
                'date' => $e->expense_date ? Carbon::parse($e->expense_date) : $e->created_at,
                'ref' => 'Exp #'.$e->id,
                'type' => 'Shop Expense',
                'detail' => ($e->category ?? '').' – '.($e->notes ?? ''),
                'amount' => $e->amount,
            ];
        }

        // Sort by date descending
        usort($rows, function ($a, $b) {
            return $b['date']->getTimestamp() - $a['date']->getTimestamp();
        });

        return view('admin.users.transactions-report', compact('user', 'rows'));
    }

    public function updateuser(Request $request, $id)
    {
        if (! function_exists('saveSingleFile')) {
            require base_path('app/Helper/helper.php');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'phone' => 'nullable|array',
            'phone.*' => 'nullable|string|max:50',
            'phone_name' => 'nullable|array',
            'phone_name.*' => 'nullable|string|max:100',
            'role' => 'required|string',
            'spatie_role' => 'nullable|string|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id_card_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id_card_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'father_id_card_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'father_id_card_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_location' => 'nullable|string',
            'house_photo_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'credit_limit' => 'nullable|numeric|min:0',
            'salary_per_day' => 'nullable|numeric|min:0',
            'salary_per_month' => 'nullable|numeric|min:0',
            'salary_percentage' => 'nullable|numeric|min:0|max:100',
            'commission' => 'nullable|integer|min:0|max:100',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx|max:5120',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $phones = is_array($request->phone) ? $request->phone : [];
        $names = is_array($request->phone_name) ? $request->phone_name : [];
        $pairs = [];
        for ($i = 0; $i < max(count($phones), count($names)); $i++) {
            $num = isset($phones[$i]) ? trim((string) $phones[$i]) : '';
            $name = isset($names[$i]) ? trim((string) $names[$i]) : '';
            if ($num !== '' || $name !== '') {
                $pairs[] = $name !== '' ? $name.'|'.$num : $num;
            }
        }
        $user->phone = ! empty($pairs) ? implode(',', $pairs) : null;
        $user->role = $request->role;
        $user->branch_id = $request->branch_id ?: null;
        $user->current_location = $request->filled('current_location') ? trim((string) $request->current_location) : null;
        $user->credit_limit = $request->filled('credit_limit') ? (float) $request->credit_limit : null;
        $user->salary_per_day = $request->filled('salary_per_day') ? (float) $request->salary_per_day : null;
        $user->salary_per_month = $request->filled('salary_per_month') ? (float) $request->salary_per_month : null;
        $user->salary_percentage = $request->filled('salary_percentage') ? (float) $request->salary_percentage : null;
        if ($request->role === 'worker') {
            $user->commission = $request->filled('commission') ? (int) $request->commission : 0;
        } elseif ($request->has('commission')) {
            $user->commission = $request->filled('commission') ? (int) $request->commission : null;
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('profile_img')) {
            $oldPath = $user->profile_img ? public_path($user->profile_img) : null;
            if ($oldPath && is_string($user->profile_img) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
            } catch (\Throwable $e) {
                \Log::warning('UserController: profile_img save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('user_id_card_front')) {
            $oldPath = $user->user_id_card_front ? public_path($user->user_id_card_front) : null;
            if ($oldPath && is_string($user->user_id_card_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->user_id_card_front = saveSingleFile($request->file('user_id_card_front'), 'user_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: user_id_card_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('user_id_card_back')) {
            $oldPath = $user->user_id_card_back ? public_path($user->user_id_card_back) : null;
            if ($oldPath && is_string($user->user_id_card_back) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->user_id_card_back = saveSingleFile($request->file('user_id_card_back'), 'user_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: user_id_card_back save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('father_id_card_front')) {
            $oldPath = $user->father_id_card_front ? public_path($user->father_id_card_front) : null;
            if ($oldPath && is_string($user->father_id_card_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->father_id_card_front = saveSingleFile($request->file('father_id_card_front'), 'father_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: father_id_card_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('father_id_card_back')) {
            $oldPath = $user->father_id_card_back ? public_path($user->father_id_card_back) : null;
            if ($oldPath && is_string($user->father_id_card_back) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->father_id_card_back = saveSingleFile($request->file('father_id_card_back'), 'father_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: father_id_card_back save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('house_photo_front')) {
            $oldPath = $user->house_photo_front ? public_path($user->house_photo_front) : null;
            if ($oldPath && is_string($user->house_photo_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->house_photo_front = saveSingleFile($request->file('house_photo_front'), 'house_photos');
            } catch (\Throwable $e) {
                \Log::warning('UserController: house_photo_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('attachments')) {
            $existing = is_array($user->attachments) ? $user->attachments : [];
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    try {
                        $existing[] = saveSingleFile($file, 'user_attachments');
                    } catch (\Throwable $e) {
                        \Log::warning('UserController: attachment save failed', ['user_id' => $id, 'file' => $file->getClientOriginalName(), 'error' => $e->getMessage()]);
                    }
                }
            }
            $user->attachments = $existing;
        }

        $user->save();

        // Assign Spatie role (for permissions) - if spatie_role provided
        if ($request->filled('spatie_role')) {
            $user->syncRoles([$request->spatie_role]);
        } else {
            $user->syncRoles([]); // Remove all Spatie roles if none selected
        }

        // Sync multiple branch access: primary branch_id + all checked branch_ids[]
        $branchIds = array_values(array_unique(array_filter(
            array_merge(
                $request->branch_id ? [$request->branch_id] : [],
                is_array($request->branch_ids) ? $request->branch_ids : []
            )
        )));
        $pivot = collect($branchIds)->mapWithKeys(fn ($bid) => [$bid => ['role' => $user->role ?? 'staff']])->all();
        $user->assignedBranches()->sync($pivot);
        // If primary branch not set but user has access branches, set first as primary
        if (! $user->branch_id && ! empty($branchIds)) {
            $user->update(['branch_id' => (int) $branchIds[0]]);
        }

        // Ensure worker has WorkerCashAccount for commission (earned → balance, paid → debit)
        if ($user->role === 'worker') {
            \App\Models\WorkerCashAccount::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
            );
        }

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    /**
     * Show all users for a specific branch
     */
    public function branchUsers($branchId)
    {
        $branch = Branch::findOrFail($branchId);

        // Get all users for this branch (where branch_id matches)
        $users = User::whereIn('role', ['user', 'manager', 'salesman', 'purchaser', 'employee', 'worker'])
            ->where('branch_id', $branchId)
            ->with(['assignedBranches'])
            ->paginate(10);

        $branches = Branch::orderBy('branch_name', 'asc')->get();

        return view('admin.users.branch-users', compact('users', 'branches', 'branch'));
    }
}
