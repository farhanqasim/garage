<form action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}" method="POST" enctype="multipart/form-data" id="customerForm">
    @if(isset($customer)) @method('PUT') @endif
    @csrf
    <div class="modal-body">
        <div class="row g-3 p-3">
            <!-- Visiting Document Upload -->
            <div class="col-md-6">
                <label for="visiting_doc" class="form-label">Visiting Document</label>
                <div class="position-relative">
                    <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control">
                </div>
                <small class="form-text text-muted">Upload visiting card or document (PDF, DOC, DOCX, or image).</small>
                <div id="visiting_preview" style="display: none; margin-top: 10px;">
                    <div id="visiting_img_container" style="display: none;">
                        <img id="visiting_img" src="" alt="Visiting Doc Preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div id="visiting_file_info" style="display: none; text-center p-3 bg-light rounded">
                        <i class="fas fa-file fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0" id="visiting_filename"></p>
                    </div>
                </div>
                @if(isset($customer) && $customer->visiting_doc)
                    <div class="existing-file mt-2 position-relative">
                        <small class="text-muted">Existing: <a href="{{ asset($customer->visiting_doc) }}" target="_blank">{{ basename($customer->visiting_doc) }}</a></small>
                        <button type="button" class="btn btn-sm btn-danger ms-2 delete-btn" onclick="toggleDelete(this, 'delete_visiting_doc')">Delete</button>
                        <input type="hidden" name="delete_visiting_doc" value="0">
                    </div>
                @endif
            </div>
            <!-- Profile Image -->
            <div class="col-md-6">
                <label for="profile_img" class="form-label">Profile Image</label>
                <div class="profile-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer;">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container">
                        <img id="profile_preview" src="{{ isset($customer) && $customer->profile_img ? asset($customer->profile_img) : '' }}" alt="Profile Preview" class="img-fluid rounded" style="max-height: 200px; {{ isset($customer) && $customer->profile_img ? '' : 'display: none;' }}">
                    </div>
                    <div class="upload-placeholder {{ isset($customer) && $customer->profile_img ? 'd-none' : '' }}">
                        <i class="fas fa-camera fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload profile image</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn {{ isset($customer) && $customer->profile_img ? 'd-none' : '' }}">Upload Image</button>
                </div>
                @if(isset($customer) && $customer->profile_img)
                    <div class="existing-image mt-2 position-relative">
                        <small class="text-muted">Existing: <a href="{{ asset($customer->profile_img) }}" target="_blank">{{ basename($customer->profile_img) }}</a></small>
                        <button type="button" class="btn btn-sm btn-danger ms-2 delete-btn" onclick="toggleDelete(this, 'delete_profile_img')">Delete</button>
                        <input type="hidden" name="delete_profile_img" value="0">
                    </div>
                @endif
            </div>
            <!-- Name & Phone (Dynamic) -->
            <div class="col-12">
                <div id="namePhoneContainer">
                    <div class="row g-3 mb-3 align-items-end name-phone-row">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="names[]" value="{{ isset($customer) ? ($customer->names[0] ?? '') : old('names.0') }}" class="form-control speech-input" placeholder="Enter name or use mic" required>
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
                            <input type="text" name="phones[]" value="{{ isset($customer) ? ($customer->phones[0] ?? '') : old('phones.0') }}" class="form-control" placeholder="Enter phone number">
                        </div>
                    </div>
                    {{-- Additional rows for edit if multiple --}}
                    {{-- Additional rows for edit if multiple --}}
                    @if(isset($customer) && is_array($customer->names) && count($customer->names) > 1)
                        @foreach(array_slice($customer->names, 1) as $index => $name)
                            @php
                                $phoneIndex = $index + 1;
                            @endphp
                            <div class="row g-3 mb-3 align-items-end name-phone-row">
                                <div class="col-md-5">
                                    <label class="form-label">Name</label>
                                    <div class="input-group">
                                        <input type="text" name="names[]" value="{{ $name }}" class="form-control speech-input" placeholder="Enter name or use mic">
                                        <button type="button" class="btn btn-outline-secondary mic-btn">
                                            <i class="fas fa-microphone"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phones[]" value="{{ $customer->phones[$phoneIndex] ?? '' }}" class="form-control" placeholder="Enter phone number">
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="addNamePhone" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add More Name & Phone
                </button>
            </div>
            <!-- Other Fields -->
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" value="{{ isset($customer) ? $customer->company : old('company') }}" class="form-control" placeholder="Enter company">
            </div>
            <div class="col-md-6">
                <label for="carnumber">Add Vehicles:</label>
                <div class="input-group">
                    <select class="form-control carnumber-select searchable-select" name="carnumber" id="carnumber">
                        <option value="">Select Services</option>
                        <option value="1" {{ (isset($customer) && $customer->carnumber == 1) ? 'selected' : '' }}>Vehicle One</option>
                    </select>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehical-add-modal">
                        <i data-feather="plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" value="{{ isset($customer) ? $customer->email : old('email') }}" class="form-control" placeholder="Enter email">
            </div>
            <div class="col-md-6">
                <label for="group_id" class="form-label">Group</label>
                <select name="group_id" class="form-select">
                    <option value="">Select Group</option>
                    <option value="1" {{ (isset($customer) && $customer->group_id == 1) ? 'selected' : '' }}>Group One</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password <small>(auto-generated for new)</small></label>
                <div class="input-group">
                    <input type="text" name="password" id="password" value="{{ isset($customer) ? '' : old('password') }}" class="form-control" readonly placeholder="Click Generate" {{ isset($customer) ? '' : 'required' }}>
                    <button type="button" id="generatePassword" class="btn btn-outline-primary" {{ isset($customer) ? 'style="display:none;"' : '' }}>Generate</button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="opening_balance" class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ isset($customer) ? $customer->opening_balance : old('opening_balance', 0) }}" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-6">
                <label for="as_of_date" class="form-label">As of Date</label>
                <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ isset($customer) ? $customer->as_of_date_formatted : old('as_of_date') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Balance Type</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" id="to_receive" value="receive" {{ (isset($customer) && $customer->balance_type == 'receive') || !isset($customer) ? 'checked' : '' }}>
                    <label class="form-check-label" for="to_receive">To Receive</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" id="to_pay" value="pay" {{ isset($customer) && $customer->balance_type == 'pay' ? 'checked' : '' }}>
                    <label class="form-check-label" for="to_pay">To Pay</label>
                </div>
            </div>
            <!-- Credit Limit - Clean Toggle -->
            <div class="col-md-6">
                <label class="form-label">Credit Limit</label>
                <div id="creditLimitDefault" class="mt-2">
                    <button type="button" id="showCreditLimitOptions" class="btn btn-link p-0 text-primary border-0 bg-transparent">
                        Set credit limit
                    </button>
                </div>
                <div id="creditLimitOptions" style="display: none;">
                    <div id="custom_limit_input" class="ms-4 mt-2">
                        <input type="number" step="0.01" name="credit_limit" value="{{ isset($customer) ? $customer->credit_limit : old('credit_limit') }}" class="form-control" placeholder="Enter credit limit">
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="credit_limit_type" id="custom" value="custom" {{ (isset($customer) && $customer->credit_limit_type == 'custom') ? 'checked' : '' }}>
                        <label class="form-check-label" for="custom">Custom</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="credit_limit_type" id="no_limit" value="no_limit" {{ (isset($customer) && $customer->credit_limit_type == 'no_limit') ? 'checked' : '' }}>
                        <label class="form-check-label" for="no_limit">No Limit</label>
                    </div>
                    <div class="mt-3">
                        <small><a href="#" id="hideCreditLimitOptions" class="text-muted">Cancel</a></small>
                    </div>
                </div>
                @if(isset($customer) && $customer->credit_limit_type == 'custom')
                    <script>document.getElementById('creditLimitDefault').style.display = 'none'; document.getElementById('creditLimitOptions').style.display = 'block';</script>
                @endif
            </div>
            <!-- Multiple Image Upload -->
            <div class="col-md-12">
                <label for="multiple_images" class="form-label">Additional Images (Multiple)</label>
                <div class="multiple-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer; min-height: 200px;">
                    <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container d-none d-flex flex-wrap justify-content-center gap-2 p-2" id="multiple_images_preview">
                        {{-- Previews will go here --}}
                    </div>
                    @php
                        $images = $customer->multiple_images ?? [];  // Already array due to cast
                    @endphp

                    @if(empty($images))
                        <div class="upload-placeholder">
                            <i class="fas fa-images fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click to upload additional images</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn">
                            Upload Images
                        </button>
                    @else
                        <div class="existing-images d-flex flex-wrap justify-content-center gap-2 p-2">
                            @foreach($images as $img)
                                <div class="text-center border rounded p-2 bg-light position-relative"
                                     style="width: 150px; height: 150px; cursor: pointer;">
                                    
                                    <img src="{{ asset($img) }}"
                                         alt="{{ basename($img) }}"
                                         class="img-fluid rounded"
                                         style="max-height: 100px; max-width: 100px; display: block; margin: 0 auto;">

                                    <small class="d-block text-muted mt-1">
                                        {{ basename($img) }}
                                    </small>

                                    <input type="checkbox"
                                           name="delete_multiple_images[]"
                                           value="{{ basename($img) }}"
                                           class="position-absolute top-0 end-0 m-1 form-check-input"
                                           title="Check to delete">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <small class="form-text text-muted">Select multiple images to upload (e.g., for gallery or references). Check to delete existing.</small>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            {{ isset($customer) ? 'Update' : 'Save' }}
        </button>
    </div>
</form>

