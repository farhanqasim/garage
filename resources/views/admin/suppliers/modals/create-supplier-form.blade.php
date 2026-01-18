<form action="{{ route('suppliers.store') }}" method="POST" enctype="multipart/form-data" id="supplierForm">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            <!-- Profile Image -->
            <div class="col-md-6">
                <label for="profile_img" class="form-label">Profile Image</label>
                <div class="profile-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer;">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <button type="button" id="cancelProfileImg" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px; z-index: 10; display: none;" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="preview-container">
                        <img id="profile_preview" src="" alt="Profile Preview" class="img-fluid rounded" style="max-height: 200px; display: none;">
                    </div>
                    <div class="upload-placeholder">
                        <i class="fas fa-camera fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload profile image</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn">Upload Image</button>
                </div>
            </div>

            <!-- Name & Phone -->
            <div class="col-12">
                <div id="namePhoneContainer">
                    <div class="row g-3 mb-3 name-phone-row">
                        <div class="col-md-6">
                            <label class="form-label">Record Voice NAME <span class="text-danger">*</span></label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-secondary mic-btn w-100">
                                    <i class="fas fa-microphone me-2"></i>Record Voice
                                </button>
                            </div>
                            <div class="input-group">
                                <input type="text" name="names[]" value="{{ old('names.0') }}" class="form-control speech-input" placeholder="Enter name or use mic" required>
                            </div>
                            <input type="hidden" name="voice_note_required" value="1" id="voiceNoteRequired">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp Number</label>
                            <div class="mb-2">
                                <select name="country_codes[]" class="form-select phone-country-code w-100" style="max-width: 200px;" data-index="0">
                                    <option value="1">🇺🇸 +1 (US/CA)</option>
                                    <option value="44">🇬🇧 +44 (UK)</option>
                                    <option value="91">🇮🇳 +91 (India)</option>
                                    <option value="92" selected>🇵🇰 +92 (Pakistan)</option>
                                    <option value="971">🇦🇪 +971 (UAE)</option>
                                    <option value="966">🇸🇦 +966 (Saudi)</option>
                                    <option value="974">🇶🇦 +974 (Qatar)</option>
                                    <option value="965">🇰🇼 +965 (Kuwait)</option>
                                    <option value="973">🇧🇭 +973 (Bahrain)</option>
                                    <option value="968">🇴🇲 +968 (Oman)</option>
                                    <option value="961">🇱🇧 +961 (Lebanon)</option>
                                    <option value="20">🇪🇬 +20 (Egypt)</option>
                                    <option value="27">🇿🇦 +27 (South Africa)</option>
                                    <option value="49">🇩🇪 +49 (Germany)</option>
                                    <option value="33">🇫🇷 +33 (France)</option>
                                    <option value="39">🇮🇹 +39 (Italy)</option>
                                    <option value="34">🇪🇸 +34 (Spain)</option>
                                    <option value="31">🇳🇱 +31 (Netherlands)</option>
                                    <option value="32">🇧🇪 +32 (Belgium)</option>
                                    <option value="41">🇨🇭 +41 (Switzerland)</option>
                                    <option value="43">🇦🇹 +43 (Austria)</option>
                                    <option value="86">🇨🇳 +86 (China)</option>
                                    <option value="81">🇯🇵 +81 (Japan)</option>
                                    <option value="82">🇰🇷 +82 (South Korea)</option>
                                    <option value="65">🇸🇬 +65 (Singapore)</option>
                                    <option value="60">🇲🇾 +60 (Malaysia)</option>
                                    <option value="62">🇮🇩 +62 (Indonesia)</option>
                                    <option value="66">🇹🇭 +66 (Thailand)</option>
                                    <option value="84">🇻🇳 +84 (Vietnam)</option>
                                    <option value="63">🇵🇭 +63 (Philippines)</option>
                                    <option value="880">🇧🇩 +880 (Bangladesh)</option>
                                    <option value="94">🇱🇰 +94 (Sri Lanka)</option>
                                    <option value="95">🇲🇲 +95 (Myanmar)</option>
                                    <option value="977">🇳🇵 +977 (Nepal)</option>
                                    <option value="880">🇧🇩 +880 (Bangladesh)</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <input type="text" name="phones[]" value="{{ old('phones.0') }}" class="form-control phone-number-input" placeholder="Enter phone number" data-index="0">
                                <button type="button" class="btn btn-success phone-whatsapp-btn" data-index="0" title="Open WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button type="button" class="btn btn-primary phone-call-btn" data-index="0" title="Call">
                                    <i class="fas fa-phone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-start mt-2">
                    <button type="button" id="addNamePhone" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add More Name & Phone
                    </button>
                </div>
            </div>

            <!-- Other Fields -->
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" value="{{ old('company') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="business_detail" class="form-label">Products / Business Detail</label>
                <div class="business-detail-tag-container position-relative">
                    <input type="text" id="business_detail_input" class="form-control" placeholder="Type product name and press Enter" autocomplete="off" spellcheck="false">
                    <div id="business_detail_suggestions" class="business-detail-suggestions"></div>
                    <div id="business_detail_tags" class="business-detail-tags mt-2"></div>
                    <input type="hidden" name="business_detail" id="business_detail" value="{{ old('business_detail') }}">
                </div>
                <small class="form-text text-muted">Type product name, get spelling suggestions, and press Enter to add as tag.</small>
            </div>
            <div class="col-md-6">
                <label for="group_id" class="form-label">Group</label>
                <select name="group_id" class="form-select">
                    <option value="">Select Group</option>
                    <option value="1">Group One</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password <small>(auto-generated)</small></label>
                <div class="input-group">
                    <input type="text" name="password" id="password" value="" class="form-control" readonly placeholder="Click Generate" required>
                    <button type="button" id="generatePassword" class="btn btn-outline-primary">Generate</button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="opening_balance" class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="as_of_date" class="form-label">As of Date</label>
                <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ old('as_of_date') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Balance Type</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="receive" id="balance_type_receive" checked>
                    <label class="form-check-label" for="balance_type_receive">To Receive</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="pay" id="balance_type_pay">
                    <label class="form-check-label" for="balance_type_pay">To Pay</label>
                </div>
            </div>

            <!-- Credit Limit -->
            <div class="col-md-6">
                <label class="form-label">Credit Limit</label>
                <div id="creditLimitDefault" class="mt-2">
                    <button type="button" id="showCreditLimitOptions" class="btn btn-link p-0 text-primary border-0 bg-transparent">
                        Set credit limit
                    </button>
                    <div class="mt-2">
                        <button type="button" id="showDescriptionOptions" class="btn btn-link p-0 text-primary border-0 bg-transparent">
                            <i class="fas fa-align-left me-1"></i>Add Description
                        </button>
                    </div>
                </div>
                <div id="creditLimitOptions" style="display: none;">
                    <div id="custom_limit_input" class="ms-4 mt-2">
                        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="form-control" placeholder="Enter credit limit">
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="credit_limit_type" id="custom" value="custom">
                        <label class="form-check-label" for="custom">Custom</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="credit_limit_type" id="no_limit" value="no_limit" checked>
                        <label class="form-check-label" for="no_limit">No Limit</label>
                    </div>
                    <div class="mt-3">
                        <small><a href="#" id="hideCreditLimitOptions" class="text-muted">Cancel</a></small>
                    </div>
                </div>
                <div id="descriptionOptions" style="display: none;" class="mt-2">
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description">{{ old('description') }}</textarea>
                    <div class="mt-2">
                        <small><a href="#" id="hideDescriptionOptions" class="text-muted">Cancel</a></small>
                    </div>
                </div>
            </div>

            <!-- Location Link -->
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <div class="input-group mb-2">
                    <input type="text" name="location_address" id="location_address" class="form-control" placeholder="Enter address or click to get current location" value="{{ old('location_address') }}">
                    <button type="button" id="getCurrentLocation" class="btn btn-outline-primary" title="Get Current Location">
                        <i class="fas fa-map-marker-alt"></i>
                    </button>
                </div>
                <input type="hidden" name="location_latitude" id="location_latitude" value="{{ old('location_latitude') }}">
                <input type="hidden" name="location_longitude" id="location_longitude" value="{{ old('location_longitude') }}">
                <div id="locationLinkContainer" style="display: none;" class="mt-2">
                    <a href="#" id="locationGoogleMapLink" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-external-link-alt me-1"></i>Open in Google Maps
                    </a>
                    <button type="button" id="clearLocation" class="btn btn-sm btn-outline-danger ms-2">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>

            <!-- Visiting Document -->
            <div class="col-md-6">
                <label for="visiting_doc" class="form-label">Visiting Document</label>
                <div class="position-relative">
                    <input type="file" name="visiting_doc" id="visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control">
                </div>
                <small class="form-text text-muted">Upload visiting card or document (PDF, DOC, DOCX, or image).</small>
                <div id="visiting_preview" style="display: none; margin-top: 10px; position: relative;">
                    <button type="button" id="cancelVisitingDoc" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px; z-index: 10;" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                    <div id="visiting_img_container" style="display: none;">
                        <img id="visiting_img" src="" alt="Visiting Doc Preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div id="visiting_file_info" style="display: none; text-center p-3 bg-light rounded">
                        <i class="fas fa-file fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0" id="visiting_filename"></p>
                    </div>
                </div>
            </div>

            <!-- Multiple Images -->
            <div class="col-md-12">
                <label for="multiple_images" class="form-label">Additional Images (Multiple)</label>
                <div class="multiple-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer; min-height: 200px;">
                    <input type="file" name="multiple_images[]" id="multiple_images" accept="image/*" multiple class="position-absolute top-0 start-0 w-100 h-100 opacity-0">
                    <div class="preview-container d-none d-flex flex-wrap justify-content-center gap-2 p-2" id="multiple_images_preview"></div>
                    <div class="upload-placeholder">
                        <i class="fas fa-images fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload additional images</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2 upload-btn">Upload Images</button>
                </div>
                <small class="form-text text-muted">Select multiple images to upload.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            Save
        </button>
    </div>
</form>

















