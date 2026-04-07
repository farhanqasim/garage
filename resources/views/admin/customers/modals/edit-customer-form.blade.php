{{-- resources/views/admin/customers/modals/edit-customer-form.blade.php --}}
<form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" id="customerForm">
    @csrf
    @method('PUT')
    <div class="modal-body">
        @php
            $user = auth()->user();
            $userBranchId = $user->branch_id ?? $user->assignedBranches()->first()?->id ?? null;
            $isBranchUser = (bool) $userBranchId && $user->role !== 'admin';
            $selectedBranchId = old('branch_id', $customer->branch_id ?? session('selected_branch_id') ?? $userBranchId);
        @endphp
        {{-- Branch: admin all with customer's branch selected, branch user only their branch --}}
        <div class="mb-3">
            <label for="edit_customer_branch_id" class="form-label small fw-bold">Branch <span class="text-danger">*</span></label>
            <select name="branch_id" id="edit_customer_branch_id" class="form-select form-select-sm" required>
                @if(isset($branches) && $branches->isNotEmpty())
                    @if($isBranchUser && $userBranchId)
                        @foreach($branches->where('id', $userBranchId) as $b)
                            <option value="{{ $b->id }}" selected>{{ $b->branch_name }}{{ $b->branch_code ? ' (' . $b->branch_code . ')' : '' }}</option>
                        @endforeach
                    @else
                        <option value="">Select branch</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (int)$selectedBranchId === (int)$b->id ? 'selected' : '' }}>{{ $b->branch_name }}{{ $b->branch_code ? ' (' . $b->branch_code . ')' : '' }}</option>
                        @endforeach
                    @endif
                @else
                    <option value="">No branches</option>
                @endif
            </select>
        </div>
        {{-- Card 1: Basic info (Name, Phone, Company, Email, Group) --}}
        <div class="card border shadow-none">
            <div class="card-body">
                <h6 class="card-title small text-muted mb-2">Basic info</h6>
                <!-- Name & Phone -->
                <div class="mb-3">
                    <div id="namePhoneContainer">
                        @forelse($customer->names as $index => $name)
                            <div class="row g-2 mb-2 align-items-end name-phone-row" data-index="{{ $index }}">
                                <div class="col-md-5">
                                    <label class="form-label small mb-0">Name <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="names[]" value="{{ $name }}" class="form-control form-control-sm speech-input" placeholder="Name or mic" required>
                                        <button type="button" class="btn btn-outline-secondary mic-btn"><i class="fas fa-microphone"></i></button>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" style="display:{{ count($customer->names) > 1 ? 'block' : 'none' }};"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-0">WhatsApp</label>
                                    <input type="text" name="phones[]" value="{{ $customer->phones[$index] ?? '' }}" class="form-control form-control-sm" placeholder="Phone">
                                </div>
                            </div>
                        @empty
                            <div class="row g-2 mb-2 align-items-end name-phone-row">
                                <div class="col-md-5">
                                    <label class="form-label small mb-0">Name <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="names[]" value="" class="form-control form-control-sm speech-input" placeholder="Name or mic" required>
                                        <button type="button" class="btn btn-outline-secondary mic-btn"><i class="fas fa-microphone"></i></button>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" style="display:none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-0">WhatsApp</label>
                                    <input type="text" name="phones[]" value="" class="form-control form-control-sm" placeholder="Phone">
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" id="addNamePhone" class="btn btn-sm btn-outline-primary mt-1"><i class="fas fa-plus me-1"></i>Add Name & Phone</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-0 fw-bold">Customer Type</label>
                        @php
                            $customerType = old('customer_type', $customer->customer_type ?? 'retail');
                            $idSuffix = $customer->id ?? 'x';
                        @endphp
                        <div class="btn-group w-100" role="group" aria-label="Customer Type">
                            <input type="radio" class="btn-check" name="customer_type" id="customer_type_retail_{{ $idSuffix }}" value="retail" {{ $customerType === 'retail' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="customer_type_retail_{{ $idSuffix }}">Retail</label>

                            <input type="radio" class="btn-check" name="customer_type" id="customer_type_wholesaler_{{ $idSuffix }}" value="wholesaler" {{ $customerType === 'wholesaler' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="customer_type_wholesaler_{{ $idSuffix }}">Wholesaler</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="company" class="form-label small mb-0">Company</label>
                        <input type="text" name="company" value="{{ old('company', $customer->company) }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label small mb-0">Email</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label for="group_id" class="form-label small mb-0">Group</label>
                        <select name="group_id" class="form-select form-select-sm">
                            <option value="">Select Group</option>
                            <option value="1" {{ ($customer->group_id ?? '') == 1 ? 'selected' : '' }}>Group One</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Documents (Visiting Doc + Profile Image) - compact --}}
        <div class="card border shadow-none">
            <div class="card-body">
                <h6 class="card-title small text-muted mb-2">Documents</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label for="visiting_doc" class="form-label small mb-0">Visiting Document</label>
                        <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control form-control-sm">
                        @if($customer->visiting_doc)
                            <a href="{{ asset($customer->visiting_doc) }}" target="_blank" class="btn btn-sm btn-link p-0 mt-1">View current</a>
                        @endif
                        <div id="visiting_preview" style="display: none; margin-top: 6px;">
                            <div id="visiting_img_container" style="display: none;">
                                <img id="visiting_img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 80px;">
                            </div>
                            <div id="visiting_file_info" style="display: none;" class="small text-muted" id="visiting_filename"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="profile_img" class="form-label small mb-0">Profile Image</label>
                        <div class="profile-upload-box border rounded p-2 bg-light position-relative text-center" style="cursor: pointer; min-height: 80px;">
                            <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                            <div class="preview-container">
                                <img id="profile_preview" src="{{ $customer->profile_img ? asset($customer->profile_img) : '' }}" alt="Profile" class="img-fluid rounded" style="max-height: 70px; {{ $customer->profile_img ? '' : 'display: none;' }}">
                            </div>
                            <div class="upload-placeholder small {{ $customer->profile_img ? 'd-none' : '' }}">
                                <i class="fas fa-camera text-muted"></i>
                                <span class="text-muted">Upload</span>
                            </div>
                        </div>
                        @if($customer->profile_img)
                            <a href="{{ asset($customer->profile_img) }}" target="_blank" class="btn btn-sm btn-link p-0 mt-1">View current</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Account & more (no Vehicles - moved to end) --}}
        <div class="card border shadow-none">
            <div class="card-body">
                <h6 class="card-title small text-muted mb-2">Account & more</h6>
                <div class="row g-2 small">
                    <div class="col-md-6">
                        <label for="password" class="form-label small mb-0">Password</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="password" id="password" value="" class="form-control form-control-sm" placeholder="Blank = keep">
                            <button type="button" id="generatePassword" class="btn btn-outline-primary btn-sm">Generate</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="opening_balance" class="form-label small mb-0">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $customer->opening_balance) }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label for="as_of_date" class="form-label small mb-0">As of Date</label>
                        <input type="text" name="as_of_date" id="as_of_date" class="form-control form-control-sm" placeholder="DD/MM/YYYY" value="{{ old('as_of_date', $customer->as_of_date_formatted ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-0">Balance Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="balance_type" value="receive" id="bt_receive_{{ $customer->id }}" {{ ($customer->balance_type ?? 'receive') == 'receive' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bt_receive_{{ $customer->id }}">To Receive</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="balance_type" value="pay" id="bt_pay_{{ $customer->id }}" {{ ($customer->balance_type ?? '') == 'pay' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bt_pay_{{ $customer->id }}">To Pay</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-0">Credit Limit</label>
                        <div id="creditLimitDefault">
                            <button type="button" id="showCreditLimitOptions" class="btn btn-link p-0 text-primary border-0 bg-transparent btn-sm">Set credit limit</button>
                        </div>
                        <div id="creditLimitOptions" style="display: {{ ($customer->credit_limit_type ?? 'no_limit') !== 'no_limit' ? 'block' : 'none' }};">
                            <div id="custom_limit_input" class="ms-2 mt-1">
                                <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="form-control form-control-sm" placeholder="Amount" style="max-width: 120px;">
                            </div>
                            <div class="form-check form-check-inline mt-1">
                                <input class="form-check-input" type="radio" name="credit_limit_type" value="custom" {{ ($customer->credit_limit_type ?? '') == 'custom' ? 'checked' : '' }}>
                                <label class="form-check-label">Custom</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="credit_limit_type" value="no_limit" {{ ($customer->credit_limit_type ?? 'no_limit') == 'no_limit' ? 'checked' : '' }}>
                                <label class="form-check-label">No Limit</label>
                            </div>
                            <small><a href="#" id="hideCreditLimitOptions" class="text-muted">Cancel</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Additional Images (separate card before Vehicles) --}}
        <div class="card border shadow-none">
            <div class="card-body">
                <h6 class="card-title small text-muted mb-2">Additional Images</h6>
                <div class="multiple-upload-box border rounded p-2 bg-light position-relative text-center" style="cursor: pointer; min-height: 80px;">
                    <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container d-flex flex-wrap justify-content-center gap-1 p-1" id="multiple_images_preview">
                        @forelse(($customer->multiple_images ?? []) as $image)
                            <div class="position-relative">
                                <img src="{{ asset($image) }}" alt="" class="rounded" style="max-height: 60px; width: auto;">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 remove-image" style="font-size: 0.7rem; width: 18px; height: 18px; line-height: 1;" data-image="{{ $image }}">×</button>
                            </div>
                        @empty
                            <div class="d-none"></div>
                        @endforelse
                    </div>
                    <div class="upload-placeholder small {{ !empty($customer->multiple_images) ? 'd-none' : '' }}">
                        <i class="fas fa-images text-muted"></i>
                        <span class="text-muted">Upload more</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Vehicles (always at end, after Additional Images) — add new vehicle for this customer --}}
        <div class="card border shadow-none" id="edit-customer-vehicles-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h6 class="card-title small text-muted mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-car-side text-primary opacity-75" style="font-size: 0.85rem;"></i>
                        Vehicles
                        <span class="badge bg-light text-muted fw-normal small">this customer</span>
                    </h6>
                </div>
                <div id="edit-customer-vehicles-grid-{{ $customer->id }}" class="edit-customer-vehicles-grid mb-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px;">
                    @if($customer->customerCars && $customer->customerCars->count() > 0)
                        @foreach($customer->customerCars as $car)
                            <div class="card border shadow-none mb-0 vehicle-tile position-relative" style="border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-color: #e2e8f0 !important;" data-car-id="{{ $car->id }}" data-customer-id="{{ $customer->id }}" data-plate="{{ $car->plate_number ?? '' }}" data-make="{{ $car->make ?? '' }}" data-model="{{ $car->model ?? '' }}" data-year="{{ $car->year ?? '' }}">
                                <div class="card-body p-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit-customer-vehicle position-absolute top-0 end-0" style="padding: 2px 6px; font-size: 0.7rem;" title="Edit vehicle"><i data-feather="edit-2" style="width: 12px; height: 12px;"></i></button>
                                    <p class="mb-0 fw-bold text-uppercase" style="color: #4a90e2; font-size: 10px; letter-spacing: 0.5px;">Active Vehicle</p>
                                    <h6 class="mb-0 fw-bold mt-1 vehicle-tile-plate" style="color: #1e3a8a; font-size: 14px;">{{ $car->plate_number ?? '—' }}</h6>
                                    <p class="mb-0 fw-semibold small vehicle-tile-make-model" style="color: #1e3a8a;">{{ strtoupper($car->make ?? '') }} {{ strtoupper($car->model ?? '') }}</p>
                                    <p class="mb-0 small text-muted vehicle-tile-year">Year: {{ $car->year ?? '—' }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="vehicles-empty-state rounded-2 border bg-light d-flex align-items-center justify-content-center text-center py-3 px-3" style="grid-column: 1 / -1; min-height: 72px; border-style: dashed !important; border-color: #dee2e6;">
                            <div>
                                <i class="fas fa-car-side text-muted mb-1" style="font-size: 1.25rem; opacity: 0.6;"></i>
                                <p class="text-muted mb-0 small">No vehicles yet. Add one below.</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="rounded-2 border bg-light p-2 p-md-3">
                    <label class="form-label small text-muted mb-1 mb-md-2">Primary vehicle &amp; add new</label>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <select class="form-select form-select-sm" name="carnumber" id="carnumber" style="max-width: 280px; min-width: 180px;">
                            <option value="">Select vehicle</option>
                            @if($customer->customerCars && $customer->customerCars->count() > 0)
                                @foreach($customer->customerCars as $car)
                                    @php
                                        $label = ($car->plate_number ?? '—') . ' - ' . trim(strtoupper($car->make ?? '') . ' ' . strtoupper($car->model ?? ''));
                                        if (!empty($car->year)) $label .= ' (' . $car->year . ')';
                                    @endphp
                                    <option value="{{ $car->id }}" {{ (isset($customer->carnumber) && $customer->carnumber == $car->id) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" class="btn btn-primary btn-sm btn-add-customer-vehicle d-inline-flex align-items-center gap-1" data-customer-id="{{ $customer->id }}" title="Add new vehicle for this customer">
                            <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                            <span>Add vehicle</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            Update
        </button>
    </div>
</form>
