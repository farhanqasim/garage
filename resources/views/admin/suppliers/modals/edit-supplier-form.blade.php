{{-- resources/views/admin/suppliers/modals/edit-supplier-form.blade.php --}}
<form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data" id="supplierForm">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row g-3 p-3">
            <!-- Visiting Document -->
            <div class="col-md-6">
                <label for="visiting_doc" class="form-label">Visiting Document</label>
                <div class="position-relative">
                    <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control">
                </div>
                <small class="form-text text-muted">Upload visiting card or document (PDF, DOC, DOCX, or image).</small>
                @if($supplier->visiting_doc)
                    <div class="mt-2">
                        <a href="{{ asset($supplier->visiting_doc) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Current Document</a>
                    </div>
                @endif
                <div id="visiting_preview" style="display: none; margin-top: 10px;">
                    <div id="visiting_img_container" style="display: none;">
                        <img id="visiting_img" src="" alt="Visiting Doc Preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div id="visiting_file_info" style="display: none; text-center p-3 bg-light rounded">
                        <i class="fas fa-file fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0" id="visiting_filename"></p>
                    </div>
                </div>
            </div>

            <!-- Profile Image -->
            <div class="col-md-6">
                <label for="profile_img" class="form-label">Profile Image</label>
                <div class="profile-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer;">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container">
                        <img id="profile_preview" src="{{ $supplier->profile_img ? asset($supplier->profile_img) : '' }}" alt="Profile Preview" class="img-fluid rounded" style="max-height: 200px; {{ $supplier->profile_img ? '' : 'display: none;' }}">
                    </div>
                    <div class="upload-placeholder {{ $supplier->profile_img ? 'd-none' : '' }}">
                        <i class="fas fa-camera fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload profile image</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn">Upload Image</button>
                </div>
                @if($supplier->profile_img)
                    <div class="mt-2">
                        <a href="{{ asset($supplier->profile_img) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Current Image</a>
                    </div>
                @endif
            </div>

            <!-- Name & Phone -->
            <div class="col-12">
                <div id="namePhoneContainer">
                    @forelse($supplier->names as $index => $name)
                        <div class="row g-3 mb-3 align-items-end name-phone-row" data-index="{{ $index }}">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="names[]" value="{{ $name }}" class="form-control speech-input" placeholder="Enter name or use mic" required>
                                    <button type="button" class="btn btn-outline-secondary mic-btn">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger remove-row" style="display:{{ count($supplier->names) > 1 ? 'block' : 'none' }};">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="phones[]" value="{{ $supplier->phones[$index] ?? '' }}" class="form-control" placeholder="Enter phone number">
                            </div>
                        </div>
                    @empty
                        <div class="row g-3 mb-3 align-items-end name-phone-row">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="names[]" value="" class="form-control speech-input" placeholder="Enter name or use mic" required>
                                    <button type="button" class="btn btn-outline-secondary mic-btn">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger remove-row" style="display:none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" name="phones[]" value="" class="form-control" placeholder="Enter phone number">
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" id="addNamePhone" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add More Name & Phone
                </button>
            </div>

            <!-- Other Fields -->
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" value="{{ old('company', $supplier->company) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="carnumber">Add Vehicles:</label>
                <div class="input-group">
                    <select class="form-control" name="carnumber" id="carnumber">
                        <option value="">Select Services</option>
                        <option value="1" {{ $supplier->carnumber == 1 ? 'selected' : '' }}>Vehicle One</option>
                    </select>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehical-add-modal">
                        <i data-feather="plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="group_id" class="form-label">Group</label>
                <select name="group_id" class="form-select">
                    <option value="">Select Group</option>
                    <option value="1" {{ ($supplier->group_id ?? '') == 1 ? 'selected' : '' }}>Group One</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password <small>(leave blank to keep current)</small></label>
                <div class="input-group">
                    <input type="text" name="password" id="password" value="" class="form-control" placeholder="Click Generate if changing">
                    <button type="button" id="generatePassword" class="btn btn-outline-primary">Generate New</button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="opening_balance" class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $supplier->opening_balance) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="as_of_date" class="form-label">As of Date</label>
                <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ old('as_of_date', $supplier->as_of_date_formatted ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Balance Type</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="receive" {{ ($supplier->balance_type ?? 'pay') == 'receive' ? 'checked' : '' }}>
                    <label class="form-check-label">To Receive</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="pay" {{ ($supplier->balance_type ?? 'pay') == 'pay' ? 'checked' : '' }}>
                    <label class="form-check-label">To Pay</label>
                </div>
            </div>

            <!-- Credit Limit -->
            <div class="col-md-6">
                <label class="form-label">Credit Limit</label>
                <div id="creditLimitDefault" class="mt-2">
                    <button type="button" id="showCreditLimitOptions" class="btn btn-link p-0 text-primary border-0 bg-transparent">
                        Set credit limit
                    </button>
                </div>
                <div id="creditLimitOptions" style="display: {{ ($supplier->credit_limit_type ?? 'no_limit') !== 'no_limit' ? 'block' : 'none' }};">
                    <div id="custom_limit_input" class="ms-4 mt-2">
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $supplier->credit_limit) }}" class="form-control" placeholder="Enter credit limit">
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="credit_limit_type" value="custom" {{ ($supplier->credit_limit_type ?? '') == 'custom' ? 'checked' : '' }}>
                        <label class="form-check-label">Custom</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="credit_limit_type" value="no_limit" {{ ($supplier->credit_limit_type ?? 'no_limit') == 'no_limit' ? 'checked' : '' }}>
                        <label class="form-check-label">No Limit</label>
                    </div>
                    <div class="mt-3">
                        <small><a href="#" id="hideCreditLimitOptions" class="text-muted">Cancel</a></small>
                    </div>
                </div>
            </div>

            <!-- Multiple Images -->
            <div class="col-md-12">
                <label for="multiple_images" class="form-label">Additional Images (Multiple)</label>
                <div class="multiple-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer; min-height: 200px;">
                    <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container d-flex flex-wrap justify-content-center gap-2 p-2" id="multiple_images_preview">
                        @forelse($supplier->multiple_images as $image)
                            <div class="position-relative">
                                <img src="{{ asset($image) }}" alt="Existing Image" class="img-fluid rounded" style="max-height: 100px; width: auto;">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image" data-image="{{ $image }}">×</button>
                            </div>
                        @empty
                            <div class="d-none"></div>
                        @endforelse
                    </div>
                    <div class="upload-placeholder {{ !empty($supplier->multiple_images) ? 'd-none' : '' }}">
                        <i class="fas fa-images fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload additional images</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn">Upload Images</button>
                </div>
                <small class="form-text text-muted">Select multiple images to upload. Existing images shown above.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            Update
        </button>
    </div>
</form>






