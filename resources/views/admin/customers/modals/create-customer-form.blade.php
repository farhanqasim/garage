<form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" id="customerForm">
    @csrf
    <div class="modal-body p-4">
        <!-- Name & Phone Section -->
        <div class="mb-3">
            <label class="form-label fw-bold mb-2">
                <i class="ti ti-user me-1"></i>Customer Name & Contact <span class="text-danger">*</span>
            </label>
            <div id="namePhoneContainer">
                <div class="row g-2 mb-2 align-items-end name-phone-row">
                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-user"></i></span>
                            <input type="text" name="names[]" value="{{ old('names.0') }}" class="form-control speech-input" placeholder="Enter name" required>
                            <button type="button" class="btn btn-outline-primary mic-btn" title="Voice Input">
                                <i class="ti ti-microphone"></i>
                            </button>
                            <button type="button" class="btn btn-danger remove-row" style="display:none;" title="Remove">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1">WhatsApp Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-phone"></i></span>
                            <input type="text" name="phones[]" value="{{ old('phones.0') }}" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="addNamePhone" class="btn btn-sm btn-outline-primary mt-2">
                <i class="ti ti-plus me-1"></i>Add More
            </button>
        </div>

        <!-- Profile Image & Company -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label for="profile_img" class="form-label fw-bold mb-1">
                    <i class="ti ti-photo me-1"></i>Profile Picture
                </label>
                <input type="file" name="profile_img" id="profile_img" accept="image/*" class="form-control mb-2">
                <input type="hidden" name="profile_img_cropped" id="profile_img_cropped">
                <div class="profile-preview-container text-center border rounded p-2 bg-light" style="min-height: 120px; display: none;">
                    <div class="position-relative d-inline-block">
                        <img id="profile_preview" src="" alt="Profile Preview" class="img-fluid rounded" style="max-height: 120px; max-width: 100%;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-profile-image" title="Remove">
                            <i class="ti ti-x"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm position-absolute bottom-0 start-50 translate-middle-x mb-1 crop-profile-image" title="Crop">
                            <i class="ti ti-crop me-1"></i>Crop
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label for="company" class="form-label fw-bold mb-1">
                    <i class="ti ti-building me-1"></i>Company Name
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-building"></i></span>
                    <input type="text" name="company" value="{{ old('company') }}" class="form-control" placeholder="Optional">
                </div>
            </div>
        </div>

        <!-- Email & Phone -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label for="email" class="form-label fw-bold mb-1">
                    <i class="ti ti-mail me-1"></i>Email
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label for="visiting_doc" class="form-label fw-bold mb-1">
                    <i class="ti ti-file me-1"></i>Document
                </label>
                <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control">
                <div id="visiting_preview" class="mt-2" style="display: none;">
                    <div id="visiting_img_container" style="display: none;">
                        <img id="visiting_img" src="" alt="Preview" class="img-fluid rounded border" style="max-height: 100px; width: auto;">
                    </div>
                    <div id="visiting_file_info" style="display: none; text-center p-2 bg-light rounded border">
                        <i class="ti ti-file text-muted d-block mb-1" style="font-size: 24px;"></i>
                        <small class="text-muted fw-bold" id="visiting_filename"></small>
                        <button type="button" class="btn btn-sm btn-danger mt-1 remove-visiting-doc">
                            <i class="ti ti-x me-1"></i>Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group -->
        <div class="mb-3">
            <label for="group_id" class="form-label fw-bold mb-1">
                <i class="ti ti-users me-1"></i>Group
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-users"></i></span>
                <select name="group_id" class="form-select">
                    <option value="">Select Group</option>
                    <option value="1">Group One</option>
                </select>
            </div>
        </div>

        <!-- Password & Balance -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label for="password" class="form-label fw-bold mb-1">
                    <i class="ti ti-key me-1"></i>Password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                    <input type="text" name="password" id="password" value="" class="form-control" readonly placeholder="Auto-generated" required>
                    <button type="button" id="generatePassword" class="btn btn-outline-primary" title="Generate">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label for="opening_balance" class="form-label fw-bold mb-1">
                    <i class="ti ti-currency-rupee me-1"></i>Opening Balance
                </label>
                <div class="input-group">
                    <span class="input-group-text">Rs.</span>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="form-control" placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Date & Balance Type -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label for="as_of_date" class="form-label fw-bold mb-1">
                    <i class="ti ti-calendar me-1"></i>As Of Date
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                    <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ old('as_of_date') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-bold mb-1">Balance Type</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="balance_type" value="receive" id="balance_receive" checked>
                    <label class="btn btn-outline-success" for="balance_receive">To Receive</label>
                    <input type="radio" class="btn-check" name="balance_type" value="pay" id="balance_pay">
                    <label class="btn btn-outline-danger" for="balance_pay">To Pay</label>
                </div>
            </div>
        </div>

        <!-- Credit Limit -->
        <div class="mb-3">
            <label class="form-label fw-bold mb-1">
                <i class="ti ti-credit-card me-1"></i>Credit Limit
            </label>
            <div id="creditLimitDefault">
                <button type="button" id="showCreditLimitOptions" class="btn btn-outline-warning btn-sm">
                    <i class="ti ti-plus me-1"></i>Set Credit Limit
                </button>
            </div>
            <div id="creditLimitOptions" style="display: none;" class="mt-2">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="credit_limit_type" value="no_limit" id="no_limit" checked>
                    <label class="form-check-label" for="no_limit">No Limit</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="credit_limit_type" value="custom" id="custom_limit">
                    <label class="form-check-label" for="custom_limit">Custom Amount</label>
                </div>
                <div id="custom_limit_input" class="mb-2" style="display: none;">
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="form-control" placeholder="Enter amount">
                    </div>
                </div>
                <button type="button" id="hideCreditLimitOptions" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-x me-1"></i>Cancel
                </button>
            </div>
        </div>

        <!-- Multiple Images -->
        <div class="mb-2">
            <label for="multiple_images" class="form-label fw-bold mb-1">
                <i class="ti ti-photo me-1"></i>Additional Images
            </label>
            <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="form-control mb-2">
            <div class="multiple-images-preview d-none d-flex flex-wrap justify-content-start gap-2 p-2 border rounded bg-light" id="multiple_images_preview"></div>
        </div>
    </div>

    <div class="modal-footer border-top p-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancel
        </button>
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            <i class="ti ti-check me-1"></i>Save
        </button>
    </div>
</form>

<!-- Image Crop Modal (Outside Form) -->
<div class="modal fade" id="imageCropModal" tabindex="-1" aria-labelledby="imageCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="imageCropModalLabel">
                    <i class="ti ti-crop me-2"></i>Crop Image
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center">
                    <img id="cropImage" src="" alt="Crop Image" style="max-width: 100%; max-height: 400px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">
                    <i class="ti ti-check me-1"></i>Crop & Save
                </button>
            </div>
        </div>
    </div>
</div>