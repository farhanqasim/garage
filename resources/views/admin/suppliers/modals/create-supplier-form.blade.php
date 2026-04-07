<form action="{{ route('suppliers.store') }}" method="POST" enctype="multipart/form-data" id="supplierForm" data-group-post-url="{{ route('post.groups') }}" data-csrf-token="{{ csrf_token() }}">
    @csrf
    <style>#addSupplierModal .group-opt-item:hover{ background-color: rgba(0,0,0,.06); }</style>
    <style>#addSupplierModal .group-opt-item.group-opt-item-full{ opacity: 0.6; cursor: not-allowed; pointer-events: none; background: #f5f5f5 !important; }</style>
    <style>#addSupplierModal .view-numbers-link-no-group{ opacity: 0.65; pointer-events: none; }</style>
    <div class="modal-body">
        <div class="row g-3">
            <!-- Profile Image: native label opens file picker; no JS needed for click -->
            <div class="col-md-6">
                <span class="form-label d-block">Profile Image</span>
                <div class="profile-upload-box text-center border rounded p-3 bg-light position-relative">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" class="d-none" aria-label="Choose profile image">
                    <button type="button" id="cancelProfileImg" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px; z-index: 10; display: none;" title="Remove" tabindex="-1">
                        <i class="fas fa-times"></i>
                    </button>
                    <label for="profile_img" class="profile-upload-label d-block text-decoration-none text-dark mb-0" style="cursor: pointer; min-height: 120px;">
                        <div class="preview-container">
                            <img id="profile_preview" src="" alt="Profile Preview" class="img-fluid rounded" style="max-height: 200px; display: none;">
                        </div>
                        <div class="upload-placeholder">
                            <i class="fas fa-camera fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click to upload profile image</p>
                        </div>
                        <span class="btn btn-primary btn-sm mt-2 upload-btn">Upload Image</span>
                    </label>
                </div>
            </div>

            <!-- Company -->
            <div class="col-md-6">
                <label for="company" class="form-label">Company <span class="text-danger">*</span></label>
                <input type="text" name="company" value="{{ old('company') }}" class="form-control" style="text-transform: uppercase;" maxlength="255" oninput="this.value=this.value.toUpperCase()" required>
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
                            <label class="form-label">WhatsApp Number <span class="text-danger">*</span></label>
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
                                <input type="text" name="phones[]" value="{{ old('phones.0') }}" class="form-control phone-number-input" placeholder="Enter phone number" data-index="0" required>
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
            <div class="col-md-6">
                <label for="group_id" class="form-label">Group</label>
                <div class="input-group group-select-wrapper position-relative">
                    <div id="group_select_trigger" class="form-control group-select-trigger text-start" style="border-radius: 6px 0 0 6px; cursor: pointer; background: #fff;" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-label="Select group">
                        <span class="group-select-text">Select Group</span>
                        <span class="float-end opacity-75">▼</span>
                    </div>
                    <select name="group_id" id="create_supplier_group_id" class="supplier-group-select d-none" aria-hidden="true" tabindex="-1">
                        <option value="">Select Group</option>
                        @foreach($groups ?? [] as $g)
                            @php $count = (int)($g->phone_numbers_count ?? 0); @endphp
                            <option value="{{ $g->id }}" data-count="{{ $count }}">{{ $g->name }} ({{ $count }})</option>
                        @endforeach
                    </select>
                    <div id="group_dropdown" class="group-select-dropdown border rounded shadow-sm bg-white" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; margin-top: 2px; max-height: 280px; overflow: hidden;">
                        <div class="p-2 border-bottom bg-light">
                            <input type="text" id="group_search_inside" class="form-control form-control-sm" placeholder="Search group…" autocomplete="off">
                        </div>
                        <div id="group_options_list" class="overflow-auto p-1" style="max-height: 220px;"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary open-universal-modal" style="border-radius: 0 6px 6px 0;" title="Edit group" data-mode="edit" data-title="Edit Group" data-fetch-route="{{ route('show.groups', ':id') }}" data-update-route="{{ route('post.groups.update', ':id') }}" data-delete-route="{{ route('post.groups.destroy', ':id') }}" data-target-select="#create_supplier_group_id"><i class="ti ti-edit"></i></button>
                </div>
                <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                    <a href="#" id="group_view_numbers_link" class="small text-primary view-numbers-link-no-group" target="_blank" rel="noopener" title="Select a group to view its numbers">View numbers in group</a>
                    <small class="text-muted">Max 250 per group; new group created when full.</small>
                </div>
                {{-- Add New Group modal: inside form so it exists when Add button is clicked --}}
                <div id="addNewGroupModal" class="modal fade" tabindex="-1" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10070; display: none; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
                    <div class="modal-dialog modal-dialog-centered modal-sm" style="margin: auto; max-width: 90%;">
                        <div class="modal-content shadow">
                            <div class="modal-header py-2">
                                <h6 class="modal-title">Add New Group</h6>
                                <button type="button" class="btn-close btn-sm" id="addNewGroupModalClose" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-2">
                                <label for="addNewGroupName" class="form-label small">Group name</label>
                                <input type="text" id="addNewGroupName" class="form-control form-control-sm" placeholder="Enter group name" maxlength="255">
                            </div>
                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-secondary btn-sm" id="addNewGroupModalCancel">Cancel</button>
                                <button type="button" id="addNewGroupSaveBtn" class="btn btn-primary btn-sm">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Fields -->
            <div class="col-md-6">
                <label for="business_detail" class="form-label">Products / Business Detail</label>
                <div class="business-detail-tag-container position-relative">
                    <input type="text" id="business_detail_input" class="form-control" placeholder="Type product name and press Enter" autocomplete="off" spellcheck="false">
                    <div id="business_detail_suggestions" class="business-detail-suggestions"></div>
                    <div id="business_detail_tags" class="business-detail-tags mt-2"></div>
                    <input type="hidden" name="business_detail" id="business_detail" value="{{ old('business_detail') }}">
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <div id="emailContainer">
                    <div class="row g-3 mb-3 email-row">
                        <div class="col-12">
                            <input type="email" name="emails[]" value="{{ old('emails.0') }}" class="form-control email-input" placeholder="Enter email address">
                        </div>
                    </div>
                </div>
                <div class="text-start mt-2">
                    <button type="button" id="addEmail" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add More Email
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password <small>(auto-generated)</small></label>
                <input type="text" name="password" id="password" value="" class="form-control" readonly placeholder="Click Generate">
                <div class="mt-2">
                    <button type="button" id="generatePassword" class="btn btn-outline-primary">Generate</button>
                </div>
            </div>
            <div class="col-md-6">
                <label for="opening_balance" class="form-label">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control" placeholder="0" value="{{ old('opening_balance', '') }}">
            </div>
            <div class="col-md-6">
                <label for="as_of_date" class="form-label">As of Date</label>
                <input type="text" name="as_of_date" id="as_of_date" class="form-control" placeholder="DD/MM/YYYY" value="{{ old('as_of_date') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Balance Type</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="receive" id="balance_type_receive">
                    <label class="form-check-label" for="balance_type_receive">To Receive</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="balance_type" value="pay" id="balance_type_pay" checked>
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

            <!-- Address -->
            <div class="col-md-6">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" id="address" class="form-control" placeholder="Enter address" value="{{ old('address') }}">
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

            <!-- Multiple Images -->
            <div class="col-md-12">
                <span class="form-label d-block">Additional Images (Multiple)</span>
                <div class="multiple-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer; min-height: 200px;" id="multiple_images_box">
                    <input type="file" name="multiple_images[]" id="multiple_images" accept="image/jpeg,image/jpg,image/png,image/webp" multiple class="d-none">
                    <div class="preview-container d-none d-flex flex-wrap justify-content-center gap-2 p-2" id="multiple_images_preview"></div>
                    <label for="multiple_images" class="upload-area-label d-flex flex-column align-items-center justify-content-center w-100 text-decoration-none text-dark" style="cursor: pointer; min-height: 160px;">
                        <div class="upload-placeholder">
                            <i class="fas fa-images fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click to upload additional images</p>
                            <small class="text-muted d-block mt-1">JPG, PNG, WebP (multiple allowed)</small>
                        </div>
                        <span class="btn btn-primary btn-sm mt-2">Upload Images</span>
                    </label>
                </div>
                <small class="form-text text-muted">Select multiple images to upload. Max recommended: 10 images.</small>
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
<script>
(function() {
    var modalId = 'addSupplierModal';
    var formId = 'supplierForm';

    function getModal() { return document.getElementById(modalId); }
    function getForm() { var m = getModal(); return m ? m.querySelector('#' + formId) : null; }
    function inModal(el) { var m = getModal(); return m && el && m.contains(el); }

    function initSupplierFormBehavior() {
        if (typeof $ === 'undefined') return;
        $(document).off('input', '#' + modalId + ' input[name="company"]').on('input', '#' + modalId + ' input[name="company"]', function() {
            this.value = this.value.toUpperCase();
        });
        $(document).off('input', '#' + modalId + ' .phone-number-input').on('input', '#' + modalId + ' .phone-number-input', function() {
            var $row = $(this).closest('.name-phone-row');
            if ($row.find('.phone-country-code').val() === '92') {
                var v = this.value.replace(/\D/g, '').slice(0, 11);
                if (this.value !== v) this.value = v;
            }
        });
        $(document).off('change', '#' + modalId + ' .phone-country-code').on('change', '#' + modalId + ' .phone-country-code', function() {
            if ($(this).val() === '92') {
                var $input = $(this).closest('.name-phone-row').find('.phone-number-input');
                var v = ($input.val() || '').replace(/\D/g, '').slice(0, 11);
                if ($input.val() !== v) $input.val(v);
            }
        });
    }

    // Group select: custom dropdown with search INSIDE the dropdown
    function initGroupSelectWithSearchInside() {
        var trigger = document.getElementById('group_select_trigger');
        var dropdown = document.getElementById('group_dropdown');
        var searchInside = document.getElementById('group_search_inside');
        var optionsList = document.getElementById('group_options_list');
        var selectEl = document.getElementById('create_supplier_group_id');
        if (!trigger || !dropdown || !searchInside || !optionsList || !selectEl) return;

        var GROUP_CAPACITY = 250;

        function getOptionItems() {
            var items = [];
            for (var i = 0; i < selectEl.options.length; i++) {
                var opt = selectEl.options[i];
                var count = parseInt(opt.getAttribute('data-count'), 10);
                if (isNaN(count)) count = 0;
                items.push({ value: opt.value, text: opt.textContent || opt.innerText, count: count });
            }
            return items;
        }

        function renderOptions(filter) {
            var q = (filter || '').trim().toLowerCase();
            var items = getOptionItems();
            var html = '';
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (q && (item.text || '').toLowerCase().indexOf(q) < 0) continue;
                var sel = selectEl.value === item.value ? ' bg-light' : '';
                var fullClass = (item.count >= GROUP_CAPACITY) ? ' group-opt-item-full' : '';
                var fullAttr = (item.count >= GROUP_CAPACITY) ? ' data-full="1"' : '';
                html += '<div class="group-opt-item p-2 rounded' + sel + fullClass + '" data-value="' + (item.value || '').replace(/"/g, '&quot;') + '" data-count="' + item.count + '"' + fullAttr + ' style="cursor: pointer;">' + (item.text || '').replace(/</g, '&lt;') + (item.count >= GROUP_CAPACITY ? ' <span class="badge bg-secondary">Full</span>' : '') + '</div>';
            }
            if (!html) {
                var term = (filter || '').trim().replace(/"/g, '&quot;').replace(/</g, '&lt;');
                html = '<div class="p-2 text-center"><button type="button" class="btn btn-primary btn-sm group-add-new-btn" data-term="' + term + '"><i class="ti ti-plus me-1"></i>Add' + (term ? ' &quot;' + term + '&quot;' : ' new group') + '</button></div>';
            }
            optionsList.innerHTML = html;
        }

        function addNewGroup(name, callback, completeCallback) {
            var form = document.getElementById('supplierForm');
            var url = (form && form.getAttribute('data-group-post-url')) || '';
            var metaToken = document.querySelector('meta[name="csrf-token"]');
            var token = (metaToken && metaToken.getAttribute('content')) || (form && form.getAttribute('data-csrf-token')) || '';
            if (!url || !token) {
                if (typeof completeCallback === 'function') completeCallback();
                if (typeof toastr !== 'undefined') toastr.error('Missing URL or token.');
                else alert('Missing URL or token.');
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.id && res.name && callback) callback(res);
                        else if (typeof completeCallback === 'function') completeCallback();
                    } catch (e) {
                        if (typeof completeCallback === 'function') completeCallback();
                        if (typeof toastr !== 'undefined') toastr.error('Invalid response.');
                        else alert('Invalid response.');
                    }
                } else {
                    var msg = 'Failed to add group.';
                    try {
                        var err = JSON.parse(xhr.responseText);
                        if (err.message) msg = err.message;
                    } catch (e) {}
                    if (typeof completeCallback === 'function') completeCallback();
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                }
            };
            xhr.onerror = function() {
                if (typeof completeCallback === 'function') completeCallback();
                if (typeof toastr !== 'undefined') toastr.error('Network error.');
                else alert('Network error.');
            };
            xhr.send('_token=' + encodeURIComponent(token) + '&name=' + encodeURIComponent(name || ''));
        }

        function addOptionToSelect(value, text) {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = text;
            opt.setAttribute('data-count', '0');
            selectEl.appendChild(opt);
        }

        function openDropdown() {
            dropdown.style.display = 'block';
            trigger.setAttribute('aria-expanded', 'true');
            renderOptions(searchInside.value);
            setTimeout(function() { searchInside.focus(); }, 50);
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            trigger.setAttribute('aria-expanded', 'false');
        }

        function setValue(value, text) {
            selectEl.value = value || '';
            var span = trigger.querySelector('.group-select-text');
            if (span) span.textContent = text || 'Select Group';
            updateViewNumbersLink();
        }

        function updateViewNumbersLink() {
            var link = document.getElementById('group_view_numbers_link');
            if (!link) return;
            var val = selectEl.value;
            if (val && val !== '') {
                link.classList.remove('d-none', 'view-numbers-link-no-group');
                link.href = '{{ route("suppliers.group-numbers") }}?group_id=' + encodeURIComponent(val);
                link.title = 'View and manage numbers in this group';
            } else {
                link.classList.add('view-numbers-link-no-group');
                link.href = '#';
                link.title = 'Select a group to view its numbers';
            }
        }

        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (dropdown.style.display === 'none' || !dropdown.style.display) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                trigger.click();
            }
        });

        searchInside.addEventListener('input', function() {
            renderOptions(this.value);
        });
        searchInside.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { closeDropdown(); trigger.focus(); }
        });

        optionsList.addEventListener('click', function(e) {
            var item = e.target.closest('.group-opt-item');
            if (item) {
                if (item.getAttribute('data-full') === '1') return;
                var val = item.getAttribute('data-value');
                var text = item.textContent || '';
                setValue(val, text);
                closeDropdown();
                updateViewNumbersLink();
                return;
            }
            var addBtn = e.target.closest('.group-add-new-btn');
            if (addBtn) {
                e.preventDefault();
                e.stopPropagation();
                var term = (addBtn.getAttribute('data-term') || '').trim().replace(/&quot;/g, '"').replace(/&lt;/g, '<');
                var nameInput = document.getElementById('addNewGroupName');
                var addModalEl = document.getElementById('addNewGroupModal');
                if (addModalEl && nameInput) {
                    nameInput.value = term;
                    addModalEl._groupAddCallback = function(res) {
                        var displayText = (res.name || nameInput.value) + ' (0)';
                        var existing = selectEl.querySelector('option[value="' + res.id + '"]');
                        if (!existing) addOptionToSelect(res.id, displayText);
                        else existing.textContent = displayText;
                        setValue(res.id, displayText);
                        closeDropdown();
                    };
                    addModalEl.style.display = 'flex';
                    addModalEl.style.visibility = 'visible';
                    addModalEl.classList.add('show');
                    setTimeout(function() { nameInput.focus(); }, 200);
                } else {
                    var name = term || window.prompt('Enter group name:') || '';
                    if (name) addNewGroup(name, function(res) {
                        var displayText = (res.name || name) + ' (0)';
                        var existing = selectEl.querySelector('option[value="' + res.id + '"]');
                        if (!existing) addOptionToSelect(res.id, displayText);
                        else existing.textContent = displayText;
                        setValue(res.id, displayText);
                        closeDropdown();
                    });
                }
            }
        });

        document.addEventListener('click', function(e) {
            var wrap = trigger.closest('.group-select-wrapper');
            if (wrap && !wrap.contains(e.target)) closeDropdown();
        });

        function hideAddNewGroupModal() {
            var addModalEl = document.getElementById('addNewGroupModal');
            if (addModalEl) {
                addModalEl.style.display = 'none';
                addModalEl.style.visibility = '';
                addModalEl.classList.remove('show');
            }
        }

        var addModalCloseBtn = document.getElementById('addNewGroupModalClose');
        var addModalCancelBtn = document.getElementById('addNewGroupModalCancel');
        var addModalSaveBtn = document.getElementById('addNewGroupSaveBtn');
        if (addModalCloseBtn) addModalCloseBtn.addEventListener('click', hideAddNewGroupModal);
        if (addModalCancelBtn) addModalCancelBtn.addEventListener('click', hideAddNewGroupModal);
        document.body.addEventListener('click', function addNewGroupSaveHandler(e) {
            if (!e.target || !e.target.closest || !e.target.closest('#addNewGroupSaveBtn')) return;
            e.preventDefault();
            e.stopPropagation();
            var saveBtn = document.getElementById('addNewGroupSaveBtn');
            var nameInput = document.getElementById('addNewGroupName');
            var name = (nameInput && nameInput.value || '').trim();
            if (!name) {
                if (typeof toastr !== 'undefined') toastr.warning('Enter group name.');
                else alert('Enter group name.');
                return;
            }
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
            }
            var addModalEl = document.getElementById('addNewGroupModal');
            var callback = addModalEl && addModalEl._groupAddCallback;
            var done = function() {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save';
                }
            };
            addNewGroup(name, function(res) {
                done();
                hideAddNewGroupModal();
                if (callback) callback(res);
                if (addModalEl) addModalEl._groupAddCallback = null;
                if (typeof toastr !== 'undefined') toastr.success('Group added.');
            }, done);
        });

        // Initial trigger text from select
        var selOpt = selectEl.options[selectEl.selectedIndex];
        if (selOpt && selOpt.value) {
            var span = trigger.querySelector('.group-select-text');
            if (span) span.textContent = selOpt.textContent || selOpt.innerText;
        }
        updateViewNumbersLink();

        function refreshSupplierGroupDropdown() {
            var url = '{{ route("groups.index") }}';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var groups = data.groups || [];
                    var currentVal = selectEl.value;
                    while (selectEl.options.length > 1) selectEl.remove(1);
                    groups.forEach(function(g) {
                        var count = parseInt(g.phone_numbers_count, 10) || 0;
                        var opt = document.createElement('option');
                        opt.value = g.id;
                        opt.setAttribute('data-count', String(count));
                        opt.textContent = (g.name || '') + ' (' + count + ')';
                        selectEl.appendChild(opt);
                    });
                    if (currentVal) {
                        var opt = selectEl.querySelector('option[value="' + currentVal + '"]');
                        if (opt) {
                            selectEl.value = currentVal;
                            var span = trigger.querySelector('.group-select-text');
                            if (span) span.textContent = opt.textContent || opt.innerText;
                        }
                    }
                    updateViewNumbersLink();
                })
                .catch(function() {});
        }

        window.refreshSupplierGroupDropdown = refreshSupplierGroupDropdown;
        window.updateSupplierGroupViewNumbersLink = updateViewNumbersLink;

        var addModal = document.getElementById('addSupplierModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                updateViewNumbersLink();
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGroupSelectWithSearchInside);
    } else {
        initGroupSelectWithSearchInside();
    }

    // Additional Images (Multiple) – click to open file picker, preview, validate, submit with form
    var ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    function initMultipleImagesUpload() {
        var form = document.querySelector('#addSupplierModal form#supplierForm') || document.querySelector('form#supplierForm') || getForm();
        if (!form) return;
        var box = form.querySelector('.multiple-upload-box');
        var input = form.querySelector('input#multiple_images');
        var previewEl = form.querySelector('#multiple_images_preview');
        var uploadAreaLabel = box ? box.querySelector('.upload-area-label') : null;
        var placeholder = box ? box.querySelector('.upload-placeholder') : null;
        var uploadBtn = box ? box.querySelector('.upload-btn, .upload-area-label .btn') : null;
        if (!box || !input || !previewEl) return;

        function openFilePicker() {
            if (input && input.click) input.click();
        }

        function showError(msg) {
            if (typeof toastr !== 'undefined') toastr.error(msg);
            else alert(msg);
        }

        function renderPreviews(files) {
            previewEl.innerHTML = '';
            previewEl.classList.remove('d-none');
            previewEl.classList.add('d-flex', 'flex-wrap', 'justify-content-center', 'gap-2', 'p-2');
            if (uploadAreaLabel) uploadAreaLabel.style.display = 'none';
            if (placeholder) placeholder.style.display = 'none';
            if (uploadBtn) uploadBtn.style.display = 'none';
            var validFiles = [];
            var invalidNames = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (ALLOWED_IMAGE_TYPES.indexOf(file.type) === -1) {
                    invalidNames.push(file.name || 'file');
                    continue;
                }
                validFiles.push(file);
                (function(f, idx) {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        var div = document.createElement('div');
                        div.className = 'position-relative border rounded p-2 bg-white';
                        div.style.width = '120px';
                        div.setAttribute('data-file-index', idx);
                        div.innerHTML = '<img src="' + (ev.target.result || '').replace(/"/g, '&quot;') + '" alt="" class="img-fluid rounded" style="max-height:100px;width:100%;object-fit:cover;">' +
                            '<small class="d-block text-muted text-truncate mt-1" style="max-width:100px;">' + (f.name || '').replace(/</g, '&lt;') + '</small>' +
                            '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-multi-preview" title="Remove"><i class="fas fa-times"></i></button>';
                        previewEl.appendChild(div);
                    };
                    reader.readAsDataURL(f);
                })(file, validFiles.length - 1);
            }
            if (invalidNames.length > 0) {
                showError('Skipped (invalid type): ' + invalidNames.join(', ') + '. Use JPG, PNG or WebP.');
            }
            if (validFiles.length === 0) {
                if (uploadAreaLabel) uploadAreaLabel.style.display = '';
                if (placeholder) placeholder.style.display = 'block';
                if (uploadBtn) uploadBtn.style.display = 'block';
                previewEl.classList.add('d-none');
                input.value = '';
                return;
            }
            var dt = new DataTransfer();
            validFiles.forEach(function(f) { dt.items.add(f); });
            input.files = dt.files;
        }

        input.addEventListener('change', function() {
            var files = this.files;
            if (!files || files.length === 0) {
                previewEl.innerHTML = '';
                previewEl.classList.add('d-none');
                if (uploadAreaLabel) uploadAreaLabel.style.display = '';
                if (placeholder) placeholder.style.display = 'block';
                if (uploadBtn) uploadBtn.style.display = 'block';
                return;
            }
            renderPreviews(Array.from(files));
        });

        previewEl.addEventListener('click', function(e) {
            var removeBtn = e.target.closest('.remove-multi-preview');
            if (!removeBtn) return;
            e.preventDefault();
            e.stopPropagation();
            var card = removeBtn.closest('[data-file-index]');
            var idx = card ? parseInt(card.getAttribute('data-file-index'), 10) : -1;
            if (idx < 0) return;
            var files = Array.from(input.files);
            files.splice(idx, 1);
            if (files.length === 0) {
                input.value = '';
                previewEl.innerHTML = '';
                previewEl.classList.add('d-none');
                if (uploadAreaLabel) uploadAreaLabel.style.display = '';
                if (placeholder) placeholder.style.display = 'block';
                if (uploadBtn) uploadBtn.style.display = 'block';
                return;
            }
            var dt = new DataTransfer();
            files.forEach(function(f) { dt.items.add(f); });
            input.files = dt.files;
            renderPreviews(files);
        });
    }

    function runMultipleImagesInit() {
        initMultipleImagesUpload();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runMultipleImagesInit);
    } else {
        runMultipleImagesInit();
    }
    // Multiple images: native <label for="multiple_images"> opens file picker; no extra click handler needed.

    // Profile image: simple preview when crop modal not available (e.g. on purchase create page)
    document.addEventListener('change', function(e) {
        if (e.target.id !== 'profile_img' || !inModal(e.target)) return;
        var modal = getModal();
        var file = e.target.files[0];
        var preview = modal ? modal.querySelector('#profile_preview') : null;
        var box = e.target.closest('.profile-upload-box');
        var placeholder = box ? box.querySelector('.upload-placeholder') : null;
        var uploadBtn = box ? box.querySelector('.upload-btn') : null;
        var cancelBtn = modal ? modal.querySelector('#cancelProfileImg') : null;
        if (!modal || !box) return;
        if (document.getElementById('imageCropModal')) return;
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                if (preview) { preview.src = ev.target.result; preview.style.display = 'block'; }
                if (placeholder) placeholder.style.display = 'none';
                if (uploadBtn) uploadBtn.style.display = 'none';
                if (cancelBtn) cancelBtn.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            if (preview) { preview.src = ''; preview.style.display = 'none'; }
            if (placeholder) placeholder.style.display = 'block';
            if (uploadBtn) uploadBtn.style.display = 'block';
            if (cancelBtn) cancelBtn.style.display = 'none';
        }
    });

    function resetSupplierModalRecordingUI(inputField, controlBtn, nameCol) {
        var form = getForm();
        if (inputField) {
            inputField.readOnly = false;
            inputField.removeAttribute('readonly');
            inputField.disabled = false;
            inputField.removeAttribute('disabled');
            inputField.style.removeProperty('color');
            inputField.style.removeProperty('backgroundColor');
            inputField.style.removeProperty('cursor');
            inputField.style.removeProperty('pointer-events');
            inputField.placeholder = 'Enter name or use mic';
            inputField.value = '';
            if (!inputField.classList.contains('speech-input')) inputField.classList.add('speech-input');
        }
        if (controlBtn && controlBtn.parentNode) {
            var newBtn = document.createElement('button');
            newBtn.type = 'button';
            newBtn.className = 'btn btn-outline-secondary mic-btn w-100';
            newBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>Record Voice';
            newBtn.disabled = false;
            controlBtn.parentNode.replaceChild(newBtn, controlBtn);
        }
        if (nameCol) {
            var ac = nameCol.querySelector('.audio-player-container');
            if (ac) ac.remove();
        }
        if (form) {
            var vn = form.querySelector('input[name="voice_note"]');
            if (vn) vn.remove();
        }
    }

    function ensureRowInputsWritable(row) {
        if (!row) return;
        var inputs = row.querySelectorAll('input:not([type="hidden"]), select, textarea');
        for (var i = 0; i < inputs.length; i++) {
            var el = inputs[i];
            el.disabled = false;
            el.removeAttribute('disabled');
            el.readOnly = false;
            el.removeAttribute('readonly');
            el.style.removeProperty('pointer-events');
            el.style.removeProperty('cursor');
        }
    }

    async function runSupplierModalMicRecording(controlBtn) {
        var modal = getModal();
        var form = getForm();
        if (!controlBtn || !modal || !form) return;
        if (controlBtn.disabled) return;
        var namePhoneRow = controlBtn.closest('.name-phone-row');
        if (!namePhoneRow) return;
        var nameCol = namePhoneRow.querySelector('.col-md-6:first-child');
        if (!nameCol) return;
        var inputGroup = nameCol.querySelector('.input-group');
        var inputField = inputGroup ? inputGroup.querySelector('input[type="text"].speech-input') : null;
        if (!inputField && inputGroup) {
            inputField = inputGroup.querySelector('input[type="text"]');
            if (inputField) inputField.classList.add('speech-input');
        }
        if (!inputField) return;
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition || !navigator.mediaDevices) {
            alert('Speech Recognition or Microphone not supported.');
            return;
        }
        var recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-US';
        recognition.maxAlternatives = 3;
        var mediaRecorder = null;
        var audioChunks = [];
        var transcript = '';
        var lastInterimTranscript = '';
        var recordingTimer = null;
        var recordingStartTime = null;
        var actualRecordingDuration = 0;
        var timeRemaining = 7;
        var isRecording = false;
        var stream = null;

        function stopRecording() {
            if (!isRecording) return;
            isRecording = false;
            if (recordingStartTime) actualRecordingDuration = Math.round((Date.now() - recordingStartTime) / 1000);
            if (recordingTimer) { clearInterval(recordingTimer); recordingTimer = null; }
            try {
                if (recognition && (recognition.state === 'listening' || recognition.state === 'running')) recognition.stop();
            } catch (err) {}
            if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
            if (stream) stream.getTracks().forEach(function(t) { t.stop(); });
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        } catch (err) {
            alert('Microphone access denied or not available.');
            return;
        }
        inputField.value = '';
        inputField.removeAttribute('readonly');
        inputField.placeholder = 'Listening... Speak now (0:07 remaining)';
        var existingAudio = nameCol.querySelector('.audio-player-container');
        if (existingAudio) existingAudio.remove();
        var existingHidden = form.querySelector('input[name="voice_note"]');
        if (existingHidden) existingHidden.remove();
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm;codecs=opus' });
        mediaRecorder.ondataavailable = function(event) {
            if (event.data && event.data.size > 0) audioChunks.push(event.data);
        };
        function applyTranscriptToField() {
            var textToUse = (transcript || lastInterimTranscript || '').trim().replace(/\s+/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            if (textToUse) inputField.value = textToUse;
        }
        mediaRecorder.onstop = function() {
            var audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            var audioURL = URL.createObjectURL(audioBlob);
            var minutes = Math.floor(actualRecordingDuration / 60);
            var seconds = actualRecordingDuration % 60;
            var durationText = minutes > 0 ? minutes + 'm ' + seconds + 's' : seconds + 's';
            var audioContainer = document.createElement('div');
            audioContainer.className = 'audio-player-container mt-2';
            audioContainer.innerHTML = '<div class="d-flex align-items-center justify-content-between mb-2"><small class="text-muted"><i class="fas fa-clock me-1"></i>Recording: <strong>' + durationText + '</strong></small><button type="button" class="btn btn-sm btn-danger cancel-audio"><i class="fas fa-trash"></i> Remove</button></div><audio controls class="w-100" preload="metadata"><source src="' + audioURL + '" type="audio/webm"></audio>';
            nameCol.appendChild(audioContainer);
            var fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'voice_note';
            fileInput.hidden = true;
            var file = new File([audioBlob], 'voice_note.webm', { type: 'audio/webm' });
            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            form.appendChild(fileInput);
            inputField.placeholder = 'Voice transcribed - ' + durationText + ' recorded';
            applyTranscriptToField();
            inputField.setAttribute('readonly', 'readonly');
            inputField.readOnly = true;
            inputField.style.backgroundColor = '#f8f9fa';
            inputField.style.cursor = 'not-allowed';
            controlBtn.innerHTML = '<i class="fas fa-play"></i>';
            controlBtn.classList.remove('mic-btn');
            controlBtn.classList.add('play-pause-btn');
            setTimeout(function() {
                applyTranscriptToField();
            }, 150);
        };
        recognition.onresult = function(event) {
            for (var i = event.resultIndex; i < event.results.length; i++) {
                var result = event.results[i];
                if (!result || !result[0]) continue;
                var t = result[0].transcript.trim().replace(/[^\w\s]/g, ' ').replace(/\s+/g, ' ').trim();
                if (!t) continue;
                var conf = result[0].confidence;
                if (conf === undefined) conf = 1;
                if (result.isFinal) {
                    t = t.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                    transcript += t + ' ';
                    lastInterimTranscript = transcript;
                    inputField.value = transcript.trim().replace(/\s+/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                } else {
                    lastInterimTranscript = transcript + t;
                    inputField.value = lastInterimTranscript.trim().replace(/\s+/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                }
            }
        };
        isRecording = true;
        recordingStartTime = Date.now();
        timeRemaining = 7;
        mediaRecorder.start(1000);
        recognition.start();
        controlBtn.innerHTML = '<i class="fas fa-stop text-danger"></i>';
        controlBtn.style.backgroundColor = '#dc3545';
        controlBtn.style.color = 'white';
        recordingTimer = setInterval(function() {
            if (!isRecording) return;
            actualRecordingDuration = Math.round((Date.now() - recordingStartTime) / 1000);
            timeRemaining--;
            var m = Math.floor(timeRemaining / 60);
            var s = timeRemaining % 60;
            var ts = m + ':' + (s < 10 ? '0' : '') + s;
            var em = Math.floor(actualRecordingDuration / 60);
            var es = actualRecordingDuration % 60;
            var elapsed = (em > 0 ? em + ':' : '0:') + (es < 10 ? '0' : '') + es;
            inputField.placeholder = 'Recording... (' + elapsed + ' / ' + ts + ' remaining)';
            if (timeRemaining <= 0) {
                clearInterval(recordingTimer);
                stopRecording();
                inputField.placeholder = 'Recording completed';
                inputField.style.backgroundColor = 'lightgreen';
                controlBtn.style.backgroundColor = '';
                controlBtn.style.color = '';
            }
        }, 1000);
        controlBtn.addEventListener('click', function stopBtnClick(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            ev.stopImmediatePropagation();
            if (isRecording) stopRecording();
            controlBtn.removeEventListener('click', stopBtnClick);
        }, { once: true });
    }

    document.addEventListener('click', function(e) {
        if (!inModal(e.target)) return;
        var modal = getModal();
        if (!modal) return;

        if (e.target.closest('.cancel-audio')) {
            var audioContainer = e.target.closest('.audio-player-container');
            if (audioContainer) {
                var nameCol = audioContainer.parentElement;
                var namePhoneRow = nameCol ? nameCol.closest('.name-phone-row') : null;
                var inputGroup = nameCol.querySelector('.input-group');
                var inputField = inputGroup ? inputGroup.querySelector('input[type="text"]') : null;
                var mb2 = nameCol.querySelector('.mb-2');
                var controlBtn = mb2 ? (mb2.querySelector('.mic-btn') || mb2.querySelector('.play-pause-btn') || mb2.querySelector('button')) : null;
                audioContainer.remove();
                if (inputField) {
                    inputField.readOnly = false;
                    inputField.removeAttribute('readonly');
                    inputField.disabled = false;
                    inputField.removeAttribute('disabled');
                    inputField.style.backgroundColor = '';
                    inputField.style.cursor = '';
                    inputField.style.pointerEvents = '';
                }
                var form = getForm();
                if (form) {
                    var vn = form.querySelector('input[name="voice_note"]');
                    if (vn) vn.remove();
                }
                if (inputField && controlBtn && nameCol) resetSupplierModalRecordingUI(inputField, controlBtn, nameCol);
                ensureRowInputsWritable(namePhoneRow);
            }
            return;
        }

        var micBtn = e.target.closest('#addSupplierModal .mic-btn');
        if (micBtn && !micBtn.classList.contains('play-pause-btn')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            runSupplierModalMicRecording(micBtn);
            return;
        }

        if (e.target.closest('#cancelProfileImg')) {
            e.preventDefault();
            var profileInput = modal.querySelector('#profile_img');
            var preview = modal.querySelector('#profile_preview');
            var placeholder = modal.querySelector('.profile-upload-box .upload-placeholder');
            var uploadBtn = modal.querySelector('.profile-upload-box .upload-btn');
            var cancelBtn = modal.querySelector('#cancelProfileImg');
            if (profileInput) { profileInput.value = ''; }
            if (preview) preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
            if (uploadBtn) uploadBtn.style.display = 'block';
            if (cancelBtn) cancelBtn.style.display = 'none';
            return;
        }

        if (e.target.closest('#generatePassword')) {
            e.preventDefault();
            var charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            var password = '';
            for (var i = 0; i < 14; i++) password += charset.charAt(Math.floor(Math.random() * charset.length));
            var passInput = modal.querySelector('#password');
            if (passInput) { passInput.value = password; passInput.removeAttribute('readonly'); }
            return;
        }

        if (e.target.closest('#addNamePhone')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var container = modal.querySelector('#namePhoneContainer');
            if (!container) return;
            var row = document.createElement('div');
            row.className = 'row g-3 mb-3 name-phone-row';
            row.innerHTML = '<div class="col-md-6"><label class="form-label">Record Voice NAME <span class="text-danger">*</span></label><div class="mb-2"><button type="button" class="btn btn-outline-secondary mic-btn w-100"><i class="fas fa-microphone me-2"></i>Record Voice</button></div><div class="input-group"><input type="text" name="names[]" class="form-control speech-input" placeholder="Enter name or use mic" required></div></div><div class="col-md-6"><label class="form-label">WhatsApp Number <span class="text-danger">*</span></label><div class="mb-2"><select name="country_codes[]" class="form-select phone-country-code w-100" style="max-width: 200px;"><option value="1">🇺🇸 +1 (US/CA)</option><option value="44">🇬🇧 +44 (UK)</option><option value="91">🇮🇳 +91 (India)</option><option value="92" selected>🇵🇰 +92 (Pakistan)</option><option value="971">🇦🇪 +971 (UAE)</option><option value="966">🇸🇦 +966 (Saudi)</option><option value="974">🇶🇦 +974 (Qatar)</option><option value="965">🇰🇼 +965 (Kuwait)</option><option value="973">🇧🇭 +973 (Bahrain)</option><option value="968">🇴🇲 +968 (Oman)</option><option value="961">🇱🇧 +961 (Lebanon)</option><option value="20">🇪🇬 +20 (Egypt)</option><option value="27">🇿🇦 +27 (South Africa)</option><option value="49">🇩🇪 +49 (Germany)</option><option value="33">🇫🇷 +33 (France)</option><option value="39">🇮🇹 +39 (Italy)</option><option value="34">🇪🇸 +34 (Spain)</option><option value="31">🇳🇱 +31 (Netherlands)</option><option value="32">🇧🇪 +32 (Belgium)</option><option value="41">🇨🇭 +41 (Switzerland)</option><option value="43">🇦🇹 +43 (Austria)</option><option value="86">🇨🇳 +86 (China)</option><option value="81">🇯🇵 +81 (Japan)</option><option value="82">🇰🇷 +82 (South Korea)</option><option value="65">🇸🇬 +65 (Singapore)</option><option value="60">🇲🇾 +60 (Malaysia)</option><option value="62">🇮🇩 +62 (Indonesia)</option><option value="66">🇹🇭 +66 (Thailand)</option><option value="84">🇻🇳 +84 (Vietnam)</option><option value="63">🇵🇭 +63 (Philippines)</option><option value="880">🇧🇩 +880 (Bangladesh)</option><option value="94">🇱🇰 +94 (Sri Lanka)</option><option value="95">🇲🇲 +95 (Myanmar)</option><option value="977">🇳🇵 +977 (Nepal)</option></select></div><div class="input-group"><input type="text" name="phones[]" class="form-control phone-number-input" placeholder="Enter phone number" required><button type="button" class="btn btn-success phone-whatsapp-btn" title="Open WhatsApp"><i class="fab fa-whatsapp"></i></button><button type="button" class="btn btn-primary phone-call-btn" title="Call"><i class="fas fa-phone"></i></button></div><div class="mt-2"><button type="button" class="btn btn-danger remove-row w-100"><i class="fas fa-trash me-2"></i>Remove</button></div></div>';
            container.appendChild(row);
            if (typeof $ !== 'undefined' && $.fn && row.querySelector('.phone-country-code') && !$(row).find('.phone-country-code').hasClass('select2-hidden-accessible')) {
                $(row).find('.phone-country-code').select2({ placeholder: 'Select Country Code', allowClear: false, width: '200px', minimumResultsForSearch: 0 });
            }
            return;
        }

        if (e.target.closest('.remove-row')) {
            var nameRow = e.target.closest('.name-phone-row');
            if (nameRow) nameRow.remove();
            return;
        }

        if (e.target.id === 'showCreditLimitOptions') {
            e.preventDefault();
            var defaultDiv = modal.querySelector('#creditLimitDefault');
            var optionsDiv = modal.querySelector('#creditLimitOptions');
            var inputDiv = modal.querySelector('#custom_limit_input');
            if (defaultDiv) defaultDiv.style.display = 'none';
            if (optionsDiv) optionsDiv.style.display = 'block';
            if (modal.querySelector('#custom')) modal.querySelector('#custom').checked = true;
            if (inputDiv) inputDiv.style.display = 'block';
            return;
        }
        if (e.target.id === 'hideCreditLimitOptions') {
            e.preventDefault();
            var optionsDiv = modal.querySelector('#creditLimitOptions');
            var defaultDiv = modal.querySelector('#creditLimitDefault');
            var inputDiv = modal.querySelector('#custom_limit_input');
            if (optionsDiv) optionsDiv.style.display = 'none';
            if (defaultDiv) defaultDiv.style.display = 'block';
            var limitInput = modal.querySelector('input[name="credit_limit"]');
            if (limitInput) limitInput.value = '';
            if (inputDiv) inputDiv.style.display = 'none';
            modal.querySelectorAll('input[name="credit_limit_type"]').forEach(function(r) { r.checked = false; });
            var noLimit = modal.querySelector('#no_limit');
            if (noLimit) noLimit.checked = true;
            return;
        }
        if (e.target.id === 'showDescriptionOptions') {
            e.preventDefault();
            var descOpt = modal.querySelector('#descriptionOptions');
            var textarea = modal.querySelector('#description');
            if (descOpt) { descOpt.style.display = 'block'; if (textarea) textarea.focus(); }
            return;
        }
        if (e.target.id === 'hideDescriptionOptions') {
            e.preventDefault();
            var descOpt = modal.querySelector('#descriptionOptions');
            if (descOpt) descOpt.style.display = 'none';
            return;
        }

        if (e.target.id === 'getCurrentLocation' || e.target.closest('#getCurrentLocation')) {
            e.preventDefault();
            var btn = e.target.closest('#getCurrentLocation') || e.target;
            var addressInput = modal.querySelector('#location_address');
            var latInput = modal.querySelector('#location_latitude');
            var lngInput = modal.querySelector('#location_longitude');
            var linkContainer = modal.querySelector('#locationLinkContainer');
            var googleMapLink = modal.querySelector('#locationGoogleMapLink');
            if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    if (addressInput) addressInput.value = lat + ', ' + lng;
                    if (googleMapLink) googleMapLink.href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
                    if (linkContainer) linkContainer.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                    if (typeof fetch !== 'undefined') {
                        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng).then(function(r) { return r.json(); }).then(function(data) {
                            if (data && data.display_name && addressInput) addressInput.value = data.display_name;
                        }).catch(function() {});
                    }
                },
                function(err) {
                    alert('Error getting location: ' + (err.message || 'Unknown'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                }
            );
            return;
        }
    });

    document.addEventListener('change', function(e) {
        if (!inModal(e.target)) return;
        var modal = getModal();
        if (!modal) return;
        if (e.target.name === 'credit_limit_type') {
            var inputDiv = modal.querySelector('#custom_limit_input');
            if (inputDiv) inputDiv.style.display = e.target.value === 'custom' ? 'block' : 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = getModal();
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function() {
                var form = getForm();
                var modal = getModal();
                if (!form || !modal) return;
                var asOf = modal.querySelector('#as_of_date');
                if (asOf) asOf.value = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).split('/').join('/');
                var genBtn = modal.querySelector('#generatePassword');
                if (genBtn) genBtn.click();
            }, false);
        }
    });

    if (typeof $ !== 'undefined' && $.fn && document.readyState !== 'loading') {
        initSupplierFormBehavior();
    } else {
        document.addEventListener('DOMContentLoaded', initSupplierFormBehavior);
    }
})();
</script>

















