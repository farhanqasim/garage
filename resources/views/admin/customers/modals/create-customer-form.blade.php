<form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" id="customerForm">
    @csrf
    <div class="modal-body p-3 p-md-4">
        <!-- Step 1: Basic Information -->
        <div class="mb-4">
            <h6 class="text-uppercase fw-bold text-primary mb-3 border-bottom pb-2 d-flex align-items-center">
                <i class="ti ti-user me-2"></i><span>Basic Information</span>
            </h6>
            
            <!-- Name & Phone Section -->
            <div class="card border-primary bg-light mb-3 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <label class="form-label fw-bold mb-3 d-block">
                        <i class="ti ti-phone me-2"></i>Customer Name & Contact <span class="text-danger">*</span>
                    </label>
                    <div id="namePhoneContainer">
                        <div class="row g-2 g-md-3 mb-3 align-items-end name-phone-row">
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted mb-1">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-user text-primary"></i></span>
                                    <input type="text" name="names[]" value="{{ old('names.0') }}" class="form-control speech-input border-start-0" placeholder="Enter customer full name" required>
                                    <button type="button" class="btn btn-outline-primary mic-btn border-start-0" title="Voice Input">
                                        <i class="ti ti-microphone"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger remove-row border-start-0" style="display:none;" title="Remove">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="ti ti-info-circle"></i> Click mic icon to use voice input
                                </small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted mb-1">WhatsApp Number</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-phone text-success"></i></span>
                                    <input type="text" name="phones[]" value="{{ old('phones.0') }}" class="form-control border-start-0" placeholder="03XX-XXXXXXX">
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="ti ti-info-circle"></i> Enter with country code if needed
                                </small>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addNamePhone" class="btn btn-sm btn-outline-primary w-100 w-md-auto">
                        <i class="ti ti-plus me-1"></i>Add Another Contact
                    </button>
                </div>
            </div>

            <!-- Profile Image & Documents -->
            <div class="row g-2 g-md-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="profile_img" class="form-label fw-bold mb-2">
                        <i class="ti ti-photo me-2"></i>Profile Picture
                    </label>
                    <div class="profile-upload-box text-center border-2 border-dashed rounded p-3 p-md-4 bg-light position-relative" style="cursor: pointer; border-color: #dee2e6 !important; min-height: 150px;">
                        <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="z-index: 10;">
                        <input type="hidden" name="profile_img_cropped" id="profile_img_cropped">
                        <div class="preview-container position-relative">
                            <img id="profile_preview" src="" alt="Profile Preview" class="img-fluid rounded shadow-sm" style="max-height: 200px; max-width: 100%; display: none;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-profile-image" style="display: none; z-index: 20;" title="Remove Image">
                                <i class="ti ti-x"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm position-absolute bottom-0 start-50 translate-middle-x mb-2 crop-profile-image" style="display: none; z-index: 20;" title="Crop Image">
                                <i class="ti ti-crop me-1"></i>Crop
                            </button>
                        </div>
                        <div class="upload-placeholder">
                            <i class="ti ti-camera text-muted mb-2 d-block" style="font-size: 40px;"></i>
                            <p class="text-muted mb-1 fw-bold small">Click to Upload</p>
                            <small class="text-muted d-block">JPG, PNG or GIF (Max 2MB)</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label for="visiting_doc" class="form-label fw-bold mb-2">
                        <i class="ti ti-file me-2"></i>Visiting Card/Document
                    </label>
                    <div class="border-2 border-dashed rounded p-3 bg-light" style="border-color: #dee2e6 !important; min-height: 150px;">
                        <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control form-control-lg mb-2">
                        <small class="text-muted d-block">
                            <i class="ti ti-info-circle"></i> PDF, DOC, DOCX or Image files
                        </small>
                        <div id="visiting_preview" style="display: none; margin-top: 10px;">
                            <div id="visiting_img_container" style="display: none;">
                                <img id="visiting_img" src="" alt="Visiting Doc Preview" class="img-fluid rounded shadow-sm" style="max-height: 120px; max-width: 100%;">
                            </div>
                            <div id="visiting_file_info" style="display: none; text-center p-2 bg-white rounded">
                                <i class="ti ti-file text-muted mb-2 d-block" style="font-size: 28px;"></i>
                                <p class="text-muted mb-0 small" id="visiting_filename"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company & Email -->
            <div class="row g-2 g-md-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="company" class="form-label fw-bold mb-1">
                        <i class="ti ti-building me-2"></i>Company/Business Name
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="ti ti-building text-primary"></i></span>
                        <input type="text" name="company" value="{{ old('company') }}" class="form-control" placeholder="Enter company name (optional)">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="email" class="form-label fw-bold mb-1">
                        <i class="ti ti-mail me-2"></i>Email Address
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="ti ti-mail text-primary"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="customer@example.com (optional)">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Additional Details -->
        <div class="mb-4">
            <h6 class="text-uppercase fw-bold text-warning mb-3 border-bottom pb-2 d-flex align-items-center">
                <i class="ti ti-settings me-2"></i><span>Additional Details</span>
            </h6>
            
            <div class="row g-2 g-md-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="group_id" class="form-label fw-bold mb-1">
                        <i class="ti ti-users me-2"></i>Customer Group
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="ti ti-users text-primary"></i></span>
                        <select name="group_id" class="form-select">
                            <option value="">-- Select Group (Optional) --</option>
                            <option value="1">Group One</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="carnumber" class="form-label fw-bold mb-1">
                        <i class="ti ti-car me-2"></i>Vehicles
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="ti ti-car text-primary"></i></span>
                        <select class="form-select" name="carnumber" id="carnumber">
                            <option value="">-- Select Vehicle (Optional) --</option>
                            <option value="1">Vehicle One</option>
                        </select>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehical-add-modal" title="Add New Vehicle">
                            <i data-feather="plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Account Settings -->
        <div class="mb-4">
            <h6 class="text-uppercase fw-bold text-success mb-3 border-bottom pb-2 d-flex align-items-center">
                <i class="ti ti-wallet me-2"></i><span>Account & Financial Settings</span>
            </h6>
            
            <div class="row g-2 g-md-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="password" class="form-label fw-bold mb-1">
                        <i class="ti ti-key me-2"></i>System Password <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="ti ti-lock text-primary"></i></span>
                        <input type="text" name="password" id="password" value="" class="form-control border-start-0" readonly placeholder="Auto-generated password" required>
                        <button type="button" id="generatePassword" class="btn btn-outline-primary border-start-0" title="Generate New Password">
                            <i class="ti ti-refresh me-1"></i><span class="d-none d-md-inline">Generate</span>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="ti ti-info-circle"></i> Password is auto-generated for security
                    </small>
                </div>
                <div class="col-12 col-md-6">
                    <label for="opening_balance" class="form-label fw-bold mb-1">
                        <i class="ti ti-currency-rupee me-2"></i>Opening Balance
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">Rs.</span>
                        <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="form-control border-start-0" placeholder="0.00">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="as_of_date" class="form-label fw-bold mb-1">
                        <i class="ti ti-calendar me-2"></i>Balance As Of Date
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="ti ti-calendar text-primary"></i></span>
                        <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ old('as_of_date') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold d-block mb-1">
                        <i class="ti ti-arrow-left-right me-2"></i>Balance Type
                    </label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="balance_type" value="receive" id="balance_receive" checked>
                        <label class="btn btn-outline-success" for="balance_receive">
                            <i class="ti ti-arrow-down me-1"></i><span class="d-none d-sm-inline">To Receive</span>
                        </label>
                        <input type="radio" class="btn-check" name="balance_type" value="pay" id="balance_pay">
                        <label class="btn btn-outline-danger" for="balance_pay">
                            <i class="ti ti-arrow-up me-1"></i><span class="d-none d-sm-inline">To Pay</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Credit Limit -->
            <div class="card border-warning bg-light mb-3 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <label class="form-label fw-bold d-block mb-3">
                        <i class="ti ti-credit-card me-2"></i>Credit Limit
                    </label>
                    <div id="creditLimitDefault">
                        <button type="button" id="showCreditLimitOptions" class="btn btn-outline-warning w-100 w-md-auto">
                            <i class="ti ti-plus me-1"></i>Set Credit Limit
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="ti ti-info-circle"></i> Click to set credit limit (optional)
                        </small>
                    </div>
                    <div id="creditLimitOptions" style="display: none;">
                        <div class="row g-2 g-md-3">
                            <div class="col-12">
                                <div class="form-check mb-2 p-3 border rounded bg-white">
                                    <input class="form-check-input" type="radio" name="credit_limit_type" value="no_limit" id="no_limit" checked>
                                    <label class="form-check-label fw-bold" for="no_limit">
                                        <i class="ti ti-infinity me-1"></i>No Limit
                                    </label>
                                </div>
                                <div class="form-check mb-2 p-3 border rounded bg-white">
                                    <input class="form-check-input" type="radio" name="credit_limit_type" value="custom" id="custom_limit">
                                    <label class="form-check-label fw-bold" for="custom_limit">
                                        <i class="ti ti-edit me-1"></i>Custom Amount
                                    </label>
                                </div>
                            </div>
                            <div id="custom_limit_input" class="col-12" style="display: none;">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0">Rs.</span>
                                    <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="form-control border-start-0" placeholder="Enter credit limit amount">
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" id="hideCreditLimitOptions" class="btn btn-sm btn-outline-secondary w-100 w-md-auto">
                                    <i class="ti ti-x me-1"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Additional Images -->
        <div class="mb-3">
            <h6 class="text-uppercase fw-bold text-info mb-3 border-bottom pb-2 d-flex align-items-center">
                <i class="ti ti-photo me-2"></i><span>Additional Images (Optional)</span>
            </h6>
            <div class="multiple-upload-box text-center border-2 border-dashed rounded p-3 p-md-4 bg-light position-relative" style="cursor: pointer; border-color: #dee2e6 !important; min-height: 180px;">
                <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="z-index: 10;">
                <div class="preview-container d-none d-flex flex-wrap justify-content-center gap-2 p-2" id="multiple_images_preview"></div>
                <div class="upload-placeholder">
                    <i class="ti ti-photo text-muted mb-2 d-block" style="font-size: 40px;"></i>
                    <p class="text-muted mb-1 fw-bold small">Click to Upload Multiple Images</p>
                    <small class="text-muted d-block">You can select multiple images at once (JPG, PNG, GIF)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Crop Modal -->
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
        </div>
    </div>

    <div class="modal-footer bg-light border-top p-3 flex-column flex-md-row gap-2">
        <button type="button" class="btn btn-secondary w-100 w-md-auto" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>Cancel
        </button>
        <button type="submit" class="btn btn-primary w-100 w-md-auto">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            <i class="ti ti-check me-1"></i>Save Customer
        </button>
    </div>
</form>