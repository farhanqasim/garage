<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeCustomerMail;
use App\Models\Branch;
use App\Models\CarCountry;
use App\Models\CarManufacturer;
use App\Models\CarModel;
use App\Models\EngineCc;
use App\Models\Group;
use App\Models\GroupPhoneNumber;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierEditHistory;
use App\Models\SupplierLedgerReconciliation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function all_suppliers(Request $request)
    {
        try {
            $edit_supplier_id = $request->get('edit');
            $return_url = $request->get('return_url');
            $perPage = 10;
            $suppliersQuery = Supplier::with(['createdBy', 'createdByBranch'])->orderBy('id');
            // If we came from purchase form with edit=ID, go to the page that contains this supplier so the edit modal exists
            if ($edit_supplier_id && is_numeric($edit_supplier_id)) {
                $editId = (int) $edit_supplier_id;
                $position = Supplier::where('id', '<=', $editId)->orderBy('id')->count();
                $page = max(1, (int) ceil($position / $perPage));
                if ($page > 1) {
                    return redirect()->route('suppliers.index', array_merge($request->only('edit', 'return_url'), ['page' => $page]));
                }
            }
            $suppliers = $suppliersQuery->paginate($perPage)->appends($request->only('edit', 'return_url'));
            $branches = Branch::orderBy('branch_name', 'asc')->get();
            $carManufacturers = CarManufacturer::orderBy('name')->get();
            $carModels = CarModel::orderBy('name')->get();
            $engineccs = EngineCc::where('status', 'active')->get();
            $carCountries = CarCountry::orderBy('name')->get();
            try {
                $groups = Group::orderBy('name')->withCount('phoneNumbers')->get();
            } catch (\Throwable $e) {
                \Log::warning('Group::withCount(phoneNumbers) failed, using groups without count: '.$e->getMessage());
                $groups = Group::orderBy('name')->get()->map(function ($g) {
                    $g->phone_numbers_count = 0;

                    return $g;
                });
            }
            $return_url = $request->get('return_url');
            $edit_supplier_id = $request->get('edit');

            return view('admin.suppliers.index', compact('suppliers', 'branches', 'carManufacturers', 'carModels', 'engineccs', 'carCountries', 'groups', 'return_url', 'edit_supplier_id'));
        } catch (\Throwable $e) {
            \Log::error('SupplierController@all_suppliers: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (config('app.debug')) {
                throw $e;
            }
            $msg = 'Something went wrong. Check storage/logs/laravel.log. Error: '.htmlspecialchars($e->getMessage());

            return response('<h1>500 Server Error</h1><p>'.$msg.'</p><p>File: '.htmlspecialchars($e->getFile()).' (line '.$e->getLine().')</p>', 500)->header('Content-Type', 'text/html; charset=UTF-8');
        }
    }

    public function group_numbers_page()
    {
        return view('admin.suppliers.group-numbers');
    }

    /**
     * Minimal edit page for iframe inside Purchase create — keeps parent URL on /purchases/create.
     */
    public function supplier_embed_edit(Request $request, Supplier $supplier)
    {
        abort_unless(auth()->check() && auth()->user()->can('update_supplier'), 403);
        try {
            $groups = Group::orderBy('name')->withCount('phoneNumbers')->get();
        } catch (\Throwable $e) {
            \Log::warning('supplier_embed_edit groups: '.$e->getMessage());
            $groups = Group::orderBy('name')->get()->map(function ($g) {
                $g->phone_numbers_count = 0;

                return $g;
            });
        }

        return view('admin.suppliers.embed-edit', compact('supplier', 'groups'));
    }

    /**
     * Read-only supplier details (used by "View" button).
     */
    public function supplier_view(Request $request, Supplier $supplier)
    {
        abort_unless(auth()->check() && auth()->user()->can('view_supplier'), 403);

        $supplier->load([
            'createdBy',
            'createdByBranch',
            'group',
        ]);

        return view('admin.suppliers.modals.view-supplier-details', compact('supplier'));
    }

    /**
     * Search products by name (for Business Detail when adding a supplier).
     */
    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json(['products' => []]);
        }
        $qLower = mb_strtolower($q);
        $pattern = '%'.$qLower.'%';
        $len = mb_strlen($q);
        $products = Product::whereRaw('LOWER(name) LIKE ?', [$pattern])
            ->orderByRaw('CASE WHEN LOWER(LEFT(name, ?)) = ? THEN 0 ELSE 1 END, name', [$len, $qLower])
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]);
        $fromSuppliers = Supplier::whereNotNull('business_detail')
            ->get(['business_detail'])
            ->flatMap(function ($s) {
                $detail = is_string($s->business_detail) ? json_decode($s->business_detail, true) : $s->business_detail;

                return is_array($detail) ? $detail : [];
            })
            ->filter()
            ->unique()
            ->values();
        foreach ($fromSuppliers as $name) {
            if (is_string($name) && mb_stripos($name, $q) !== false) {
                $products->push(['id' => null, 'name' => $name]);
            }
        }
        $products = $products->unique('name')->values();

        // Merge Google Suggest-style suggestions (more keywords like Google)
        try {
            $response = Http::timeout(2)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; rv:109.0) Gecko/20100101 Firefox/119.0'])
                ->get('https://suggestqueries.google.com/complete/search', ['client' => 'firefox', 'q' => $q, 'hl' => 'en']);
            if ($response->successful()) {
                $body = $response->body();
                $arr = $response->json();
                if (! is_array($arr) || ! isset($arr[1])) {
                    if (preg_match('/\[.*?,\s*\[(.*)\]\s*\]/s', $body, $m)) {
                        $inner = '['.$m[1].']';
                        $decoded = json_decode($inner);
                        if (is_array($decoded)) {
                            $arr = [1 => $decoded];
                        }
                    }
                }
                if (is_array($arr) && isset($arr[1]) && is_array($arr[1])) {
                    $existingLower = $products->pluck('name')->map(fn ($n) => mb_strtolower($n))->flip();
                    foreach ($arr[1] as $suggestion) {
                        $name = is_string($suggestion) ? trim($suggestion) : null;
                        if ($name !== '' && strlen($name) <= 255 && ! $existingLower->has(mb_strtolower($name))) {
                            $products->push(['id' => null, 'name' => $name]);
                            $existingLower->put(mb_strtolower($name), true);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore; we still have DB + supplier products + large frontend list
        }

        $products = $products->unique('name')->values();

        return response()->json(['products' => $products]);
    }

    /**
     * Store a new product (for Business Detail). Uses consistent spelling (ucwords). Returns existing if name matches (case-insensitive).
     */
    public function storeProduct(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $name = trim($request->name);
        $normalized = ucwords(mb_strtolower($name));
        $existing = Product::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            return response()->json(['id' => $existing->id, 'name' => $existing->name]);
        }
        $product = Product::create([
            'name' => $normalized,
            'status' => 'active',
            'type' => null,
        ]);

        return response()->json(['id' => $product->id, 'name' => $product->name]);
    }

    /**
     * Normalize product names in business_detail for consistent spelling (ucwords).
     */
    private function normalizeBusinessDetailProducts($value): array
    {
        // Accept array, JSON array string, single text, or comma/newline separated text.
        if (is_array($value)) {
            $arr = $value;
        } elseif (is_string($value)) {
            $raw = trim($value);
            if ($raw === '') {
                $arr = [];
            } else {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $arr = $decoded;
                } else {
                    $arr = preg_split('/[\r\n,]+/', $raw) ?: [];
                }
            }
        } else {
            $arr = [];
        }

        $normalized = array_map(function ($name) {
            if (! is_scalar($name)) {
                return null;
            }
            $text = trim((string) $name);
            if ($text === '') {
                return null;
            }

            return ucwords(mb_strtolower($text));
        }, $arr);

        $normalized = array_values(array_filter($normalized, fn ($v) => $v !== null && $v !== ''));

        return array_values(array_unique($normalized));
    }

    public function supplier_store(Request $request)
    {
        $request->validate([
            'company' => 'required|string|max:255',
            'names' => 'required|array',
            'names.0' => 'required|string|max:255',
            'phones' => 'required|array',
            'phones.0' => 'required|string|max:50',
            'group_id' => 'nullable|exists:groups,id',
        ], [
            'company.required' => 'Company name is required.',
            'names.required' => 'At least one name is required.',
            'names.0.required' => 'Record Voice Name is required.',
            'phones.required' => 'At least one phone number is required.',
            'phones.0.required' => 'WhatsApp Number is required.',
            'group_id.exists' => 'Selected group is invalid.',
        ]);

        $email = ! empty($request->emails) ? trim($request->emails[0] ?? $request->email ?? '') : trim($request->email ?? '');
        if ($email !== '' && Supplier::where('email', $email)->exists()) {
            $msg = 'A supplier with this email address already exists. Please use a different email or edit the existing supplier.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['email' => [$msg]]], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        try {
            $plainPassword = $request->password ?? Str::random(12);

            $supplier = new Supplier;
            $supplier->names = $request->names ?? [];
            $supplier->phones = array_filter($request->phones ?? []);
            $supplier->emails = array_filter($request->emails ?? []);
            // Keep single email field for backward compatibility
            $supplier->email = ! empty($request->emails) ? ($request->emails[0] ?? $request->email) : $request->email;
            $supplier->company = $request->company;
            $supplier->address = $request->address;
            $supplier->business_detail = $request->has('business_detail')
                ? $this->normalizeBusinessDetailProducts($request->business_detail)
                : [];
            $supplier->carnumber = $request->carnumber;
            $supplier->group_id = $request->group_id;
            $supplier->opening_balance = (float) ($request->opening_balance ?? 0);
            // Convert DD/MM/YYYY to YYYY-MM-DD format
            if ($request->as_of_date) {
                try {
                    $supplier->as_of_date = Carbon::createFromFormat('d/m/Y', $request->as_of_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    // If conversion fails, try to parse as is (in case it's already in YYYY-MM-DD format)
                    try {
                        $supplier->as_of_date = Carbon::parse($request->as_of_date)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $supplier->as_of_date = null;
                    }
                }
            } else {
                $supplier->as_of_date = null;
            }
            $supplier->balance_type = $request->balance_type ?? 'pay';
            $supplier->password = Hash::make($plainPassword);
            $supplier->credit_limit_type = $request->credit_limit_type ?? 'no_limit';
            $supplier->credit_limit = $request->credit_limit_type === 'custom' ? $request->credit_limit : null;

            // Save who created this supplier and from which branch
            if (Auth::check()) {
                $supplier->created_by = Auth::id();
            }
            if (session('selected_branch_id')) {
                $supplier->branch_id = session('selected_branch_id');
            }

            if ($request->hasFile('profile_img')) {
                $supplier->profile_img = saveSingleFile($request->file('profile_img'), 'Supplier_img');
            }

            if ($request->hasFile('visiting_doc')) {
                $supplier->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Supplier_docs');
            }

            if ($request->hasFile('multiple_images')) {
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                $files = $request->file('multiple_images');
                $invalid = [];
                foreach ($files as $f) {
                    if (! in_array($f->getMimeType(), $allowedMimes, true)) {
                        $invalid[] = $f->getClientOriginalName();
                    }
                }
                if (! empty($invalid)) {
                    return redirect()->back()->withInput()->with('error', 'Invalid image type: '.implode(', ', $invalid).'. Use JPG, PNG or WebP.');
                }
                $multipleImages = saveMultipleFiles($request->file('multiple_images'), 'Supplier_images');
                $supplier->multiple_images = $multipleImages;
            }

            if ($request->hasFile('voice_note')) {
                $supplier->voice_note = saveSingleFile($request->file('voice_note'), 'Supplier_audio');
            }

            $supplier->save();

            if ($request->group_id && ! empty(array_filter($request->phones ?? []))) {
                $groupId = (int) $request->group_id;
                $group = Group::resolveGroupForNewNumbers($groupId);
                $supplier->group_id = (string) $group->id;
                $supplier->save();
                $countryCodes = $request->country_codes ?? [];
                foreach (array_values(array_filter($request->phones ?? [])) as $i => $phone) {
                    $phone = trim((string) $phone);
                    if ($phone === '') {
                        continue;
                    }
                    $group = Group::resolveGroupForNewNumbers($group->id);
                    GroupPhoneNumber::updateOrCreate(
                        [
                            'group_id' => $group->id,
                            'phone_number' => $phone,
                        ],
                        [
                            'supplier_id' => $supplier->id,
                            'country_code' => $countryCodes[$i] ?? null,
                            'company_name' => $request->company,
                            'is_frozen' => false,
                        ]
                    );
                }
            }

            if ($supplier->email) {
                try {
                    Mail::to($supplier->email)->send(new WelcomeCustomerMail($supplier->email, $plainPassword));
                } catch (\Throwable $mailEx) {
                    \Log::warning('Welcome email failed for supplier: '.$mailEx->getMessage());
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                $names = is_array($supplier->names) ? $supplier->names : (json_decode($supplier->names ?? '[]', true) ?? []);
                $phones = is_array($supplier->phones) ? $supplier->phones : (json_decode($supplier->phones ?? '[]', true) ?? []);
                $businessDetail = is_array($supplier->business_detail) ? $supplier->business_detail : (json_decode($supplier->business_detail ?? '[]', true) ?? []);

                return response()->json([
                    'success' => true,
                    'message' => 'Supplier Added Successfully',
                    'supplier' => [
                        'id' => $supplier->id,
                        'company' => $supplier->company ?? '',
                        'names' => $names,
                        'phones' => $phones,
                        'address' => $supplier->address ?? '',
                        'area' => $supplier->area ?? '',
                        'business_detail' => $businessDetail,
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Supplier Added Successfully');

        } catch (QueryException $e) {
            $msg = 'Something went wrong while saving. Please try again or check storage/logs/laravel.log.';
            $code = $e->getCode();
            $message = $e->getMessage();
            if ($code === '23000' || str_contains($message, 'Duplicate entry')) {
                if (str_contains($message, 'suppliers_email_unique') || str_contains($message, 'email')) {
                    $msg = 'A supplier with this email address already exists. Please use a different email or edit the existing supplier.';
                }
            }
            \Log::error('SupplierController@supplier_store: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => ['email' => [$msg]]], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        } catch (\Throwable $e) {
            \Log::error('SupplierController@supplier_store: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = config('app.debug')
                ? $e->getMessage().' in '.$e->getFile().':'.$e->getLine()
                : 'Something went wrong while saving. Please try again or check storage/logs/laravel.log.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => []], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }
    }

    public function supplier_update(Request $request, Supplier $supplier)
    {
        $plainPassword = $request->password ? $request->password : null;

        // Store original values to track changes (get fresh copy)
        $supplier->refresh();
        $originalData = $supplier->getAttributes();
        $changes = [];

        // Track changes for each field
        $fieldsToTrack = [
            'names' => 'Names',
            'phones' => 'Phones',
            'company' => 'Company',
            'email' => 'Email',
            'address' => 'Address',
            'carnumber' => 'Car Number',
            'group_id' => 'Group',
            'opening_balance' => 'Opening Balance',
            'as_of_date' => 'As of Date',
            'balance_type' => 'Balance Type',
            'credit_limit_type' => 'Credit Limit Type',
            'credit_limit' => 'Credit Limit',
            'profile_img' => 'Profile Image',
            'visiting_doc' => 'Visiting Document',
            'voice_note' => 'Voice Note',
        ];

        // Store old values before updating
        $oldNames = $supplier->names;
        $oldPhones = $supplier->phones;
        $oldEmails = $supplier->emails;
        $oldCompany = $supplier->company;
        $oldEmail = $supplier->email;
        $oldAddress = $supplier->address;
        $oldCarnumber = $supplier->carnumber;
        $oldGroupId = $supplier->group_id;
        $oldOpeningBalance = $supplier->opening_balance;
        $oldAsOfDate = $supplier->as_of_date;
        $oldBalanceType = $supplier->balance_type;
        $oldCreditLimitType = $supplier->credit_limit_type;
        $oldCreditLimit = $supplier->credit_limit;
        $oldProfileImg = $supplier->profile_img;
        $oldVisitingDoc = $supplier->visiting_doc;
        $oldVoiceNote = $supplier->voice_note;

        $supplier->names = $request->names ?? $supplier->names;
        $supplier->phones = array_filter($request->phones ?? $supplier->phones);
        $supplier->company = $request->company ?? $supplier->company;
        $supplier->email = $request->email ?? $supplier->email;
        $supplier->address = $request->address ?? $supplier->address;
        $supplier->carnumber = $request->carnumber ?? $supplier->carnumber;
        $supplier->group_id = $request->group_id ?? $supplier->group_id;
        $supplier->opening_balance = $request->opening_balance ?? $supplier->opening_balance;
        if ($request->has('business_detail')) {
            $supplier->business_detail = $this->normalizeBusinessDetailProducts($request->business_detail);
        }
        // Convert DD/MM/YYYY to YYYY-MM-DD format
        if ($request->has('as_of_date') && $request->as_of_date) {
            try {
                $supplier->as_of_date = Carbon::createFromFormat('d/m/Y', $request->as_of_date)->format('Y-m-d');
            } catch (\Exception $e) {
                // If conversion fails, try to parse as is (in case it's already in YYYY-MM-DD format)
                try {
                    $supplier->as_of_date = Carbon::parse($request->as_of_date)->format('Y-m-d');
                } catch (\Exception $e2) {
                    // Keep existing date if conversion fails
                    $supplier->as_of_date = $supplier->as_of_date;
                }
            }
        }
        $supplier->balance_type = $request->balance_type ?? $supplier->balance_type;
        $supplier->credit_limit_type = $request->credit_limit_type ?? $supplier->credit_limit_type;
        $supplier->credit_limit = $request->credit_limit_type === 'custom' ? ($request->credit_limit ?? $supplier->credit_limit) : null;
        if ($plainPassword) {
            $supplier->password = Hash::make($plainPassword);
        }
        if ($request->hasFile('profile_img')) {
            // Delete old image if exists
            if ($supplier->profile_img && file_exists(public_path($supplier->profile_img))) {
                unlink(public_path($supplier->profile_img));
            }
            $supplier->profile_img = saveSingleFile($request->file('profile_img'), 'Supplier_img');
            $changes['profile_img'] = [
                'old' => $oldProfileImg ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Profile Image',
            ];
        }
        if ($request->hasFile('visiting_doc')) {
            // Delete old document if exists
            if ($supplier->visiting_doc && file_exists(public_path($supplier->visiting_doc))) {
                unlink(public_path($supplier->visiting_doc));
            }
            $supplier->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Supplier_docs');
            $changes['visiting_doc'] = [
                'old' => $oldVisitingDoc ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Visiting Document',
            ];
        }
        $existingImages = $supplier->multiple_images;
        if (! is_array($existingImages)) {
            $decodedExistingImages = is_string($existingImages) ? json_decode($existingImages, true) : [];
            $existingImages = is_array($decodedExistingImages) ? $decodedExistingImages : [];
        }
        $oldImageCount = count($existingImages);

        // Remove only images explicitly requested by user.
        $removeImages = array_values(array_filter((array) $request->input('remove_multiple_images', []), function ($path) {
            return is_string($path) && trim($path) !== '';
        }));
        if (! empty($removeImages)) {
            $existingImages = array_values(array_filter($existingImages, function ($path) use ($removeImages) {
                return ! in_array($path, $removeImages, true);
            }));
            foreach ($removeImages as $removedPath) {
                $fullPath = public_path($removedPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        if ($request->hasFile('multiple_images')) {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $files = $request->file('multiple_images');
            $invalid = [];
            foreach ($files as $f) {
                if (! in_array($f->getMimeType(), $allowedMimes, true)) {
                    $invalid[] = $f->getClientOriginalName();
                }
            }
            if (! empty($invalid)) {
                return redirect()->back()->withInput()->with('error', 'Invalid image type: '.implode(', ', $invalid).'. Use JPG, PNG or WebP.');
            }
            $newImages = saveMultipleFiles($files, 'Supplier_images');
            $existingImages = array_merge($existingImages, $newImages);
        }

        // Persist filtered/appended image list even when no new uploads were sent.
        $supplier->multiple_images = array_values($existingImages);
        $newImageCount = count($supplier->multiple_images);
        if ($newImageCount !== $oldImageCount || ! empty($removeImages) || $request->hasFile('multiple_images')) {
            $changes['multiple_images'] = [
                'old' => $oldImageCount.' image(s)',
                'new' => $newImageCount.' image(s)',
                'label' => 'Multiple Images',
            ];
        }

        if ($request->hasFile('voice_note')) {
            // Delete old voice note if exists
            if ($supplier->voice_note && file_exists(public_path($supplier->voice_note))) {
                unlink(public_path($supplier->voice_note));
            }
            $supplier->voice_note = saveSingleFile($request->file('voice_note'), 'Supplier_audio');
            $changes['voice_note'] = [
                'old' => $oldVoiceNote ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Voice Note',
            ];
        }

        // Track changes for all text fields (file fields are handled separately above)
        $fieldMappings = [
            'names' => ['old' => $oldNames, 'new' => $supplier->names, 'label' => 'Names'],
            'phones' => ['old' => $oldPhones, 'new' => $supplier->phones, 'label' => 'Phones'],
            'emails' => ['old' => $oldEmails, 'new' => $supplier->emails, 'label' => 'Emails'],
            'company' => ['old' => $oldCompany, 'new' => $supplier->company, 'label' => 'Company'],
            'email' => ['old' => $oldEmail, 'new' => $supplier->email, 'label' => 'Email'],
            'address' => ['old' => $oldAddress, 'new' => $supplier->address, 'label' => 'Address'],
            'carnumber' => ['old' => $oldCarnumber, 'new' => $supplier->carnumber, 'label' => 'Car Number'],
            'group_id' => ['old' => $oldGroupId, 'new' => $supplier->group_id, 'label' => 'Group'],
            'opening_balance' => ['old' => $oldOpeningBalance, 'new' => $supplier->opening_balance, 'label' => 'Opening Balance'],
            'as_of_date' => ['old' => $oldAsOfDate, 'new' => $supplier->as_of_date, 'label' => 'As of Date'],
            'balance_type' => ['old' => $oldBalanceType, 'new' => $supplier->balance_type, 'label' => 'Balance Type'],
            'credit_limit_type' => ['old' => $oldCreditLimitType, 'new' => $supplier->credit_limit_type, 'label' => 'Credit Limit Type'],
            'credit_limit' => ['old' => $oldCreditLimit, 'new' => $supplier->credit_limit, 'label' => 'Credit Limit'],
        ];

        foreach ($fieldMappings as $field => $data) {
            $oldValue = $data['old'];
            $newValue = $data['new'];

            // Handle array fields
            if (in_array($field, ['names', 'phones'])) {
                $oldValue = is_array($oldValue) ? implode(', ', $oldValue) : ($oldValue ?? 'N/A');
                $newValue = is_array($newValue) ? implode(', ', $newValue) : ($newValue ?? 'N/A');
            }

            // Format values for display
            $oldValue = $oldValue ?? 'N/A';
            $newValue = $newValue ?? 'N/A';

            // Only track if value actually changed
            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'label' => $data['label'],
                ];
            }
        }

        // Track password change separately (don't store actual password)
        if ($plainPassword) {
            $changes['password'] = [
                'old' => '******',
                'new' => '****** (Changed)',
                'label' => 'Password',
            ];
        }

        $supplier->save();

        // Save edit history if there are any changes
        if (! empty($changes)) {
            SupplierEditHistory::create([
                'supplier_id' => $supplier->id,
                'edited_by' => Auth::id(),
                'branch_id' => session('selected_branch_id'),
                'changes' => $changes,
                'notes' => $request->notes ?? null,
            ]);
        }

        if ($request->group_id && ! empty(array_filter($request->phones ?? []))) {
            $groupId = (int) $request->group_id;
            $group = Group::resolveGroupForNewNumbers($groupId);
            $supplier->group_id = (string) $group->id;
            $supplier->save();
            $countryCodes = $request->country_codes ?? [];
            foreach (array_values(array_filter($request->phones ?? [])) as $i => $phone) {
                $phone = trim((string) $phone);
                if ($phone === '') {
                    continue;
                }
                $group = Group::resolveGroupForNewNumbers($group->id);
                GroupPhoneNumber::updateOrCreate(
                    [
                        'group_id' => $group->id,
                        'phone_number' => $phone,
                    ],
                    [
                        'supplier_id' => $supplier->id,
                        'country_code' => $countryCodes[$i] ?? null,
                        'company_name' => $request->company,
                        'is_frozen' => false,
                    ]
                );
            }
        }

        if ($plainPassword && $supplier->email) {
            Mail::to($supplier->email)->send(new WelcomeCustomerMail($supplier->email, $plainPassword));
        }

        if ($request->boolean('embedded_purchase_context') && $request->wantsJson()) {
            $supplier->refresh();
            $names = is_array($supplier->names) ? $supplier->names : (json_decode($supplier->names ?? '[]', true) ?? []);
            $phones = is_array($supplier->phones) ? $supplier->phones : (json_decode($supplier->phones ?? '[]', true) ?? []);
            $name0 = $names[0] ?? '';
            $phone0 = $phones[0] ?? '';
            $company = (string) ($supplier->company ?? '');
            $display = $company !== '' ? $company.' - '.$name0 : $name0;
            if ($display === '' || $display === ' - ') {
                $display = $name0 !== '' ? $name0 : 'N/A';
            }
            if ($phone0 !== '') {
                $display .= ' - '.$phone0;
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully.',
                'supplier' => [
                    'id' => $supplier->id,
                    'company' => $company,
                    'name' => $name0,
                    'phone' => $phone0,
                    'display' => $display,
                ],
            ]);
        }

        $returnUrl = $request->get('return_url');
        if (is_string($returnUrl)) {
            $returnUrl = trim(urldecode($returnUrl));
        }
        if ($returnUrl && $this->isSafeReturnUrl($returnUrl)) {
            $sid = (string) $supplier->id;
            if (preg_match('/[?&]supplier_id=\d+/', $returnUrl)) {
                $returnUrl = preg_replace('/([?&])supplier_id=\d+/', '$1supplier_id='.$sid, $returnUrl, 1);
            } else {
                $sep = strpos($returnUrl, '?') !== false ? '&' : '?';
                $returnUrl = $returnUrl.$sep.'supplier_id='.$sid;
            }

            return redirect()->to($returnUrl)->with('success', 'Supplier updated successfully. Your selection has been preserved.');
        }

        return redirect()->back()->with('success', 'Supplier updated successfully.');
    }

    /**
     * Allow redirect only to relative path or same host (prevents open redirect).
     */
    private function isSafeReturnUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }
        $url = trim($url);
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }
        try {
            $parsed = parse_url($url);
            $host = strtolower((string) ($parsed['host'] ?? ''));
            $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
            $reqHost = strtolower((string) request()->getHost());
            if ($host === '') {
                return true;
            }

            return $host === $appHost || $host === $reqHost;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get edit history for a supplier
     */
    public function getEditHistory(Supplier $supplier)
    {
        $history = $supplier->editHistory()->with(['editedBy', 'branch'])->get();

        return response()->json([
            'success' => true,
            'history' => $history->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->formatted_date,
                    'time' => $item->formatted_time,
                    'edited_by' => $item->editedBy ? $item->editedBy->name : 'N/A',
                    'branch' => $item->branch ? $item->branch->branch_name : 'N/A',
                    'changes' => $item->changes,
                    'notes' => $item->notes,
                ];
            }),
        ]);
    }

    /**
     * Return the latest tally (reconciliation) date for the supplier ledger. Used by "Last Tally" quick filter.
     * Always reads from database; no hardcoded dates.
     */
    public function getSupplierLastTallyDate(Supplier $supplier)
    {
        $last = SupplierLedgerReconciliation::where('supplier_id', $supplier->id)
            ->whereNotNull('reconciled_at')
            ->orderByDesc('reconciled_at')
            ->value('reconciled_at');

        $date = $last ? \Carbon\Carbon::parse($last)->format('Y-m-d') : null;

        return response()->json([
            'success' => true,
            'last_tally_date' => $date,
        ]);
    }

    /**
     * Get Supplier Ledger Report (تاریخ، اشیاء/بل، ادائیگی، ریٹرن، موجودہ بیلنس)
     * Query: date_from, date_to (Y-m-d) optional — filter transactions to this range and set opening balance as at start of range.
     */
    public function getSupplierLedger(Request $request, Supplier $supplier)
    {
        $openingBalance = (float) ($supplier->opening_balance ?? 0);
        $balanceType = $supplier->balance_type ?? 'pay';
        $dateFrom = $request->get('date_from') ? \Carbon\Carbon::parse($request->get('date_from'))->format('Y-m-d') : null;
        $dateTo = $request->get('date_to') ? \Carbon\Carbon::parse($request->get('date_to'))->format('Y-m-d') : null;

        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch'])
            ->orderBy('purchase_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $payments = Payment::where('supplier_id', $supplier->id)
            ->orderBy('payment_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $events = [];
        foreach ($purchases as $purchase) {
            $grandTotal = (float) $purchase->grand_total;
            $isReturn = $purchase->status === 'return' || $grandTotal < 0;
            $amount = $isReturn ? abs($grandTotal) : $grandTotal;
            $events[] = [
                'sort_at' => $purchase->purchase_date->format('Y-m-d').' '.$purchase->created_at->format('H:i:s'),
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'type' => ($isReturn ? 'Return' : 'Purchase'),
                'reference' => $purchase->reference ?? $purchase->invoice_no ?? 'N/A',
                'invoice_no' => $purchase->invoice_no,
                'purchase_id' => $purchase->id,
                'payment_id' => null,
                'description' => 'Purchase Invoice #'.$purchase->invoice_no,
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
                'debit' => 0,
                'credit' => 0,
                'amount' => $amount,
                'is_payment' => false,
                'is_return' => $isReturn,
            ];
        }
        foreach ($payments as $payment) {
            $amount = (float) ($payment->amount ?? 0);
            if ($amount <= 0) {
                continue;
            }
            $events[] = [
                'sort_at' => $payment->payment_date->format('Y-m-d').' '.($payment->created_at ? $payment->created_at->format('H:i:s') : '00:00:00'),
                'date' => $payment->payment_date->format('d/m/Y'),
                'time' => $payment->created_at ? $payment->created_at->format('h:i A') : '',
                'type' => 'Payment',
                'reference' => $payment->transaction_id ?? ('Payment #'.$payment->id),
                'invoice_no' => null,
                'purchase_id' => null,
                'payment_id' => $payment->id,
                'description' => 'Payment'.($payment->transaction_id ? ' '.$payment->transaction_id : ''),
                'branch' => '-',
                'debit' => 0,
                'credit' => 0,
                'amount' => $amount,
                'is_payment' => true,
                'is_return' => false,
            ];
        }
        usort($events, function ($a, $b) {
            return strcmp($a['sort_at'], $b['sort_at']);
        });

        $periodOpeningBalance = $openingBalance;
        if ($dateFrom !== null && $dateTo !== null) {
            $periodOpeningBalance = $openingBalance;
            foreach ($events as $ev) {
                $evDate = substr($ev['sort_at'], 0, 10);
                if ($evDate >= $dateFrom) {
                    break;
                }
                if ($ev['is_payment']) {
                    if ($balanceType == 'pay') {
                        $periodOpeningBalance -= $ev['amount'];
                    } else {
                        $periodOpeningBalance += $ev['amount'];
                    }
                } else {
                    if ($ev['is_return']) {
                        if ($balanceType == 'pay') {
                            $periodOpeningBalance -= $ev['amount'];
                        } else {
                            $periodOpeningBalance += $ev['amount'];
                        }
                    } else {
                        if ($balanceType == 'pay') {
                            $periodOpeningBalance += $ev['amount'];
                        } else {
                            $periodOpeningBalance -= $ev['amount'];
                        }
                    }
                }
            }
            $events = array_values(array_filter($events, function ($ev) use ($dateFrom, $dateTo) {
                $d = substr($ev['sort_at'], 0, 10);

                return $d >= $dateFrom && $d <= $dateTo;
            }));
        }

        $transactions = [];
        $runningBalance = $periodOpeningBalance;
        foreach ($events as $ev) {
            $debit = 0;
            $credit = 0;
            if ($ev['is_payment']) {
                if ($balanceType == 'pay') {
                    $credit = $ev['amount'];
                    $runningBalance -= $ev['amount'];
                } else {
                    $debit = $ev['amount'];
                    $runningBalance += $ev['amount'];
                }
            } else {
                if ($ev['is_return']) {
                    if ($balanceType == 'pay') {
                        $credit = $ev['amount'];
                        $runningBalance -= $ev['amount'];
                    } else {
                        $debit = $ev['amount'];
                        $runningBalance += $ev['amount'];
                    }
                } else {
                    if ($balanceType == 'pay') {
                        $debit = $ev['amount'];
                        $runningBalance += $ev['amount'];
                    } else {
                        $credit = $ev['amount'];
                        $runningBalance -= $ev['amount'];
                    }
                }
            }
            $transactions[] = [
                'date' => $ev['date'],
                'time' => $ev['time'],
                'type' => $ev['type'],
                'reference' => $ev['reference'],
                'invoice_no' => $ev['invoice_no'],
                'purchase_id' => $ev['purchase_id'] ?? null,
                'payment_id' => $ev['payment_id'] ?? null,
                'description' => $ev['description'],
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
                'branch' => $ev['branch'],
            ];
        }

        $endingBalance = $runningBalance;
        try {
            $reconciliations = SupplierLedgerReconciliation::where('supplier_id', $supplier->id)
                ->with('reconciledByUser')
                ->get()
                ->map(function ($r) {
                    $key = $r->purchase_id ? 'purchase_'.$r->purchase_id : 'payment_'.$r->payment_id;

                    return [
                        'key' => $key,
                        'purchase_id' => $r->purchase_id,
                        'payment_id' => $r->payment_id,
                        'reconciled_by_name' => $r->reconciledByUser ? $r->reconciledByUser->name : null,
                        'reconciled_at' => $r->reconciled_at ? $r->reconciled_at->format('d/m/Y H:i') : null,
                        'image_url' => $r->image_path ? asset('storage/'.$r->image_path) : null,
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('getSupplierLedger: tallies load failed: '.$e->getMessage());
            $reconciliations = collect();
        }
        $res = [
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->names[0] ?? 'N/A',
                'email' => $supplier->email ?? 'N/A',
                'phone' => $supplier->phones[0] ?? 'N/A',
            ],
            'opening_balance' => number_format($periodOpeningBalance, 0),
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'reconciliations' => $reconciliations,
            'ending_balance' => number_format($endingBalance, 0),
            'total_debit' => number_format(collect($transactions)->sum('debit'), 0),
            'total_credit' => number_format(collect($transactions)->sum('credit'), 0),
        ];
        if ($dateFrom !== null) {
            $res['date_from'] = \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
        }
        if ($dateTo !== null) {
            $res['date_to'] = \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
        }

        if ($request->boolean('include_bill_details')) {
            $purchaseIds = array_values(array_unique(array_filter(array_column($events, 'purchase_id'))));
            if (! empty($purchaseIds)) {
                $bills = Purchase::whereIn('id', $purchaseIds)
                    ->with(['items.item', 'branch'])
                    ->orderBy('purchase_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
                $res['bill_details'] = $bills->map(function ($purchase) {
                    $items = $purchase->items->map(function ($pi) {
                        return [
                            'product_name' => $pi->item ? ($pi->item->name ?? $pi->item->bar_code ?? 'N/A') : 'N/A',
                            'quantity' => (float) $pi->quantity,
                            'unit' => $pi->unit ?? '-',
                            'rate' => (float) $pi->rate,
                            'item_total' => (float) $pi->total_cost,
                        ];
                    })->values()->all();

                    return [
                        'purchase_id' => $purchase->id,
                        'invoice_no' => $purchase->invoice_no ?? 'N/A',
                        'reference' => $purchase->reference ?? $purchase->invoice_no ?? 'N/A',
                        'date' => $purchase->purchase_date ? $purchase->purchase_date->format('d/m/Y') : '',
                        'branch' => $purchase->branch ? $purchase->branch->branch_name : '-',
                        'grand_total' => (float) $purchase->grand_total,
                        'items' => $items,
                    ];
                })->values()->all();
            } else {
                $res['bill_details'] = [];
            }
        }

        return response()->json($res);
    }

    /**
     * Get Purchase Detail History for Supplier
     */
    public function getPurchaseDetailHistory(Supplier $supplier)
    {
        // Get all purchases with items for this supplier
        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch', 'items.item'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $detailHistory = [];

        foreach ($purchases as $purchase) {
            $items = [];
            foreach ($purchase->items as $item) {
                $items[] = [
                    'item_name' => $item->item ? $item->item->name : 'N/A',
                    'barcode' => $item->item ? ($item->item->barcode ?? 'N/A') : 'N/A',
                    'quantity' => number_format($item->quantity, 2),
                    'unit' => $item->unit ?? 'pcs',
                    'rate' => number_format($item->rate, 2),
                    'discount' => number_format($item->discount, 2),
                    'tax_percentage' => number_format($item->tax_percentage, 2),
                    'tax_amount' => number_format($item->tax_amount, 2),
                    'unit_cost' => number_format($item->unit_cost, 2),
                    'total_cost' => number_format($item->total_cost, 2),
                ];
            }

            $detailHistory[] = [
                'purchase_id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
                'reference' => $purchase->reference ?? 'N/A',
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
                'status' => $purchase->status,
                'subtotal' => number_format($purchase->subtotal, 2),
                'discount' => number_format($purchase->discount, 2),
                'order_tax' => number_format($purchase->order_tax, 2),
                'shipping' => number_format($purchase->shipping, 2),
                'grand_total' => number_format($purchase->grand_total, 2),
                'description' => $purchase->description ?? 'N/A',
                'items' => $items,
            ];
        }

        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->names[0] ?? 'N/A',
            ],
            'detail_history' => $detailHistory,
        ]);
    }

    /**
     * Generate Supplier Ledger PDF (same data as getSupplierLedger; optional date_from, date_to)
     */
    public function generateSupplierLedgerPDF(Request $request, Supplier $supplier)
    {
        $openingBalance = (float) ($supplier->opening_balance ?? 0);
        $balanceType = $supplier->balance_type ?? 'pay';
        $dateFrom = $request->get('date_from') ? Carbon::parse($request->get('date_from'))->format('Y-m-d') : null;
        $dateTo = $request->get('date_to') ? Carbon::parse($request->get('date_to'))->format('Y-m-d') : null;

        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch'])
            ->orderBy('purchase_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $payments = Payment::where('supplier_id', $supplier->id)
            ->orderBy('payment_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $events = [];
        foreach ($purchases as $purchase) {
            $grandTotal = (float) $purchase->grand_total;
            $isReturn = $purchase->status === 'return' || $grandTotal < 0;
            $amount = $isReturn ? abs($grandTotal) : $grandTotal;
            $events[] = [
                'sort_at' => $purchase->purchase_date->format('Y-m-d').' '.$purchase->created_at->format('H:i:s'),
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'type' => ($isReturn ? 'Return' : 'Purchase'),
                'reference' => $purchase->reference ?? $purchase->invoice_no ?? 'N/A',
                'description' => 'Purchase Invoice #'.$purchase->invoice_no,
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
                'amount' => $amount,
                'is_payment' => false,
                'is_return' => $isReturn,
            ];
        }
        foreach ($payments as $payment) {
            $amount = (float) ($payment->amount ?? 0);
            if ($amount <= 0) {
                continue;
            }
            $events[] = [
                'sort_at' => $payment->payment_date->format('Y-m-d').' '.($payment->created_at ? $payment->created_at->format('H:i:s') : '00:00:00'),
                'date' => $payment->payment_date->format('d/m/Y'),
                'time' => $payment->created_at ? $payment->created_at->format('h:i A') : '',
                'type' => 'Payment',
                'reference' => $payment->transaction_id ?? ('Payment #'.$payment->id),
                'description' => 'Payment'.($payment->transaction_id ? ' '.$payment->transaction_id : ''),
                'branch' => '-',
                'amount' => $amount,
                'is_payment' => true,
                'is_return' => false,
            ];
        }
        usort($events, function ($a, $b) {
            return strcmp($a['sort_at'], $b['sort_at']);
        });

        $periodOpeningBalance = $openingBalance;
        if ($dateFrom !== null && $dateTo !== null) {
            foreach ($events as $ev) {
                $evDate = substr($ev['sort_at'], 0, 10);
                if ($evDate >= $dateFrom) {
                    break;
                }
                if ($ev['is_payment']) {
                    if ($balanceType == 'pay') {
                        $periodOpeningBalance -= $ev['amount'];
                    } else {
                        $periodOpeningBalance += $ev['amount'];
                    }
                } else {
                    if ($ev['is_return']) {
                        if ($balanceType == 'pay') {
                            $periodOpeningBalance -= $ev['amount'];
                        } else {
                            $periodOpeningBalance += $ev['amount'];
                        }
                    } else {
                        if ($balanceType == 'pay') {
                            $periodOpeningBalance += $ev['amount'];
                        } else {
                            $periodOpeningBalance -= $ev['amount'];
                        }
                    }
                }
            }
            $events = array_values(array_filter($events, function ($ev) use ($dateFrom, $dateTo) {
                $d = substr($ev['sort_at'], 0, 10);

                return $d >= $dateFrom && $d <= $dateTo;
            }));
        } else {
            $periodOpeningBalance = $openingBalance;
        }

        $transactions = [];
        $runningBalance = $periodOpeningBalance;
        foreach ($events as $ev) {
            $debit = 0;
            $credit = 0;
            if ($ev['is_payment']) {
                if ($balanceType == 'pay') {
                    $credit = $ev['amount'];
                    $runningBalance -= $ev['amount'];
                } else {
                    $debit = $ev['amount'];
                    $runningBalance += $ev['amount'];
                }
            } else {
                if ($ev['is_return']) {
                    if ($balanceType == 'pay') {
                        $credit = $ev['amount'];
                        $runningBalance -= $ev['amount'];
                    } else {
                        $debit = $ev['amount'];
                        $runningBalance += $ev['amount'];
                    }
                } else {
                    if ($balanceType == 'pay') {
                        $debit = $ev['amount'];
                        $runningBalance += $ev['amount'];
                    } else {
                        $credit = $ev['amount'];
                        $runningBalance -= $ev['amount'];
                    }
                }
            }
            $transactions[] = [
                'date' => $ev['date'],
                'time' => $ev['time'],
                'type' => $ev['type'],
                'reference' => $ev['reference'],
                'description' => $ev['description'],
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
                'branch' => $ev['branch'],
            ];
        }

        $endingBalance = $runningBalance;
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');

        $data = [
            'supplier' => [
                'name' => $supplier->names[0] ?? 'N/A',
                'email' => $supplier->email ?? 'N/A',
                'phone' => $supplier->phones[0] ?? 'N/A',
            ],
            'opening_balance' => $periodOpeningBalance,
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'ending_balance' => $endingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'generated_at' => Carbon::now()->format('d/m/Y h:i A'),
        ];
        if ($dateFrom !== null) {
            $data['date_from'] = Carbon::parse($dateFrom)->format('d/m/Y');
        }
        if ($dateTo !== null) {
            $data['date_to'] = Carbon::parse($dateTo)->format('d/m/Y');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.suppliers.pdf.ledger', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download('Supplier_Ledger_'.str_replace(' ', '_', $supplier->names[0] ?? 'Supplier').'_'.date('Y-m-d').'.pdf');
    }

    /**
     * Store a ledger tally (image + who/when) for a specific ledger row (purchase or payment).
     */
    public function storeLedgerReconciliation(Request $request, Supplier $supplier)
    {
        try {
            $request->validate([
                'purchase_id' => 'nullable|integer|exists:purchases,id',
                'payment_id' => 'nullable|integer|exists:payments,id',
                'balance_at_reconcile' => 'nullable|numeric',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ], [
                'image.required' => 'Please attach an image for tally.',
                'image.image' => 'The file must be an image.',
                'image.max' => 'Image must not exceed 5MB.',
            ]);

            if (! $request->purchase_id && ! $request->payment_id) {
                return response()->json(['success' => false, 'message' => 'Either purchase_id or payment_id is required.'], 422);
            }

            if ($request->purchase_id) {
                $purchase = Purchase::find($request->purchase_id);
                if (! $purchase || (int) $purchase->supplier_id !== (int) $supplier->id) {
                    return response()->json(['success' => false, 'message' => 'Purchase does not belong to this supplier.'], 422);
                }
            }
            if ($request->payment_id) {
                $payment = Payment::find($request->payment_id);
                if (! $payment || (int) $payment->supplier_id !== (int) $supplier->id) {
                    return response()->json(['success' => false, 'message' => 'Payment does not belong to this supplier.'], 422);
                }
            }

            $path = $request->file('image')->store('supplier-ledger-reconciliations', 'public');
            if (! $path) {
                return response()->json(['success' => false, 'message' => 'Image could not be saved. Please ensure storage is linked (run: php artisan storage:link).']);
            }

            SupplierLedgerReconciliation::updateOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'purchase_id' => $request->purchase_id ?: null,
                    'payment_id' => $request->payment_id ?: null,
                ],
                [
                    'balance_at_reconcile' => $request->balance_at_reconcile,
                    'image_path' => $path,
                    'reconciled_by' => Auth::id(),
                    'reconciled_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Tally saved.',
                'image_url' => asset('storage/'.$path),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('storeLedgerReconciliation: '.$e->getMessage(), [
                'supplier_id' => $supplier->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $message = 'Could not save tally. ';
            if (str_contains($e->getMessage(), 'No such file or directory') || str_contains($e->getMessage(), 'storage')) {
                $message .= 'Please run: php artisan storage:link';
            } elseif (preg_match('/table.*doesn\'t exist|SQLSTATE.*42S02/', $e->getMessage())) {
                $message .= 'Database may need update. Please run: php artisan migrate';
            } elseif (config('app.debug')) {
                $message .= $e->getMessage();
            } else {
                $message .= 'Please try again or contact support.';
            }

            return response()->json(['success' => false, 'message' => $message], 500);
        }
    }

    public function supplier_delete(Supplier $supplier)
    {
        // Delete files before deleting the record
        if ($supplier->profile_img && file_exists(public_path($supplier->profile_img))) {
            unlink(public_path($supplier->profile_img));
        }
        if ($supplier->visiting_doc && file_exists(public_path($supplier->visiting_doc))) {
            unlink(public_path($supplier->visiting_doc));
        }
        if ($supplier->voice_note && file_exists(public_path($supplier->voice_note))) {
            unlink(public_path($supplier->voice_note));
        }
        if (! empty($supplier->multiple_images)) {
            foreach ($supplier->multiple_images as $imagePath) {
                if (file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
            }
        }

        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier Deleted Successfully');
    }
}
