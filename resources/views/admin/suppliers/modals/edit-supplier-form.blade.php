{{-- resources/views/admin/suppliers/modals/edit-supplier-form.blade.php --}}
<form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" enctype="multipart/form-data" id="{{ !empty($embed_mode) ? 'supplierEditEmbedForm' : 'supplierForm' }}">
    @csrf
    @method('PUT')
    @if(!empty($embed_mode))
        <input type="hidden" name="embedded_purchase_context" value="1">
    @endif
    @if(!empty($return_url))
        <input type="hidden" name="return_url" value="{{ $return_url }}">
    @endif
    <div class="modal-body">
        <div class="row g-3 p-3">
            <!-- Profile Image -->
            <div class="col-md-6">
                <label for="profile_img" class="form-label">Profile Image</label>
                <div class="profile-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer;">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 supplier-profile-file">
                    <div class="preview-container">
                        <img id="profile_preview" src="{{ $supplier->profile_img ? asset($supplier->profile_img) : '' }}" alt="Profile Preview" class="img-fluid rounded supplier-profile-preview" style="max-height: 200px; width: 100%; object-fit: contain; {{ $supplier->profile_img ? '' : 'display: none;' }}" onerror="this.onerror=null; this.style.display='none';">
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

            <!-- Company (below Name & Phone / WhatsApp section) -->
            <div class="col-12">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" value="{{ old('company', $supplier->company) }}" class="form-control">
            </div>

            <!-- Address -->
            <div class="col-12">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" id="address" class="form-control" placeholder="Enter address" value="{{ old('address', $supplier->address ?? '') }}">
            </div>

            <!-- Other Fields -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control">
            </div>
            <div class="col-md-6 edit-business-detail-container" data-supplier-id="{{ $supplier->id }}">
                <label for="business_detail_input_edit_{{ $supplier->id }}" class="form-label">Products / Business Detail</label>
                <div class="business-detail-tag-container position-relative">
                    <input type="text" id="business_detail_input_edit_{{ $supplier->id }}" class="form-control edit-business-detail-input" placeholder="Type product name and press Enter" autocomplete="off" spellcheck="false">
                    <div id="business_detail_suggestions_edit_{{ $supplier->id }}" class="business-detail-suggestions edit-business-detail-suggestions"></div>
                    <div id="business_detail_tags_edit_{{ $supplier->id }}" class="business-detail-tags mt-2 edit-business-detail-tags"></div>
                    @php
                        $bd = $supplier->business_detail ?? [];
                        $bdJson = is_array($bd) ? json_encode($bd) : (is_string($bd) ? $bd : '[]');
                    @endphp
                    <input type="hidden" name="business_detail" id="business_detail_edit_{{ $supplier->id }}" value="{{ $bdJson }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="group_id" class="form-label">Group</label>
                <div class="input-group group-select-wrapper position-relative">
                    <select name="group_id" class="form-select supplier-group-select" style="border-radius: 6px 0 0 6px;">
                        <option value="">Select Group</option>
                        @foreach($groups ?? [] as $g)
                            @php $count = (int)($g->phone_numbers_count ?? 0); @endphp
                            <option value="{{ $g->id }}" data-count="{{ $count }}" {{ ($supplier->group_id ?? '') == $g->id ? 'selected' : '' }} {{ $count >= 250 ? 'disabled' : '' }}>{{ $g->name }} ({{ $count }}){{ $count >= 250 ? ' — Full' : '' }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary open-universal-modal" style="border-radius: 0 6px 6px 0;" title="Edit group" data-mode="edit" data-title="Edit Group" data-fetch-route="{{ route('show.groups', ':id') }}" data-update-route="{{ route('post.groups.update', ':id') }}" data-delete-route="{{ route('post.groups.destroy', ':id') }}" data-target-select=".supplier-group-select"><i class="ti ti-edit"></i></button>
                </div>
                <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                    <a href="#" class="edit-supplier-group-view-numbers-link small text-primary d-none" target="_blank" rel="noopener" title="Select a group to view its numbers">View numbers in group</a>
                    <small class="text-muted">Max 250 per group; new group created when full.</small>
                </div>
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
                <label for="edit_multiple_images_{{ $supplier->id }}" class="form-label">Additional Images (Multiple)</label>
                <div class="multiple-upload-box text-center border rounded p-3 bg-light position-relative" style="cursor: pointer; min-height: 200px;">
                    <input type="file" name="multiple_images[]" id="edit_multiple_images_{{ $supplier->id }}" accept="image/jpeg,image/jpg,image/png,image/webp" multiple class="d-none supplier-edit-multiple-images-input">
                    <div class="preview-container d-flex flex-wrap justify-content-center gap-2 p-2 supplier-existing-images-preview">
                        @forelse(($supplier->multiple_images ?? []) as $image)
                            @if(!empty($image))
                            <div class="position-relative border rounded p-2 bg-white" style="width: 120px;">
                                <img src="{{ asset($image) }}" alt="Existing Image" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentElement.innerHTML='';">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-existing-image" data-image="{{ $image }}" title="Remove">×</button>
                                <small class="d-block text-muted mt-1">Existing</small>
                            </div>
                            @endif
                        @empty
                            <div class="d-none"></div>
                        @endforelse
                    </div>
                    <div class="preview-container d-none flex-wrap justify-content-center gap-2 p-2 supplier-new-images-preview"></div>
                    <div class="upload-placeholder {{ !empty($supplier->multiple_images) ? 'd-none' : '' }}">
                        <i class="fas fa-images fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Click to upload additional images</p>
                    </div>
                    <label for="edit_multiple_images_{{ $supplier->id }}" class="btn btn-primary btn-sm mt-2 upload-btn supplier-edit-upload-images-btn mb-0" data-target-input="#edit_multiple_images_{{ $supplier->id }}">Upload Images</label>
                </div>
                <small class="form-text text-muted">Select multiple images to upload. Existing images shown above.</small>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        @if(!empty($embed_mode))
            <button type="button" class="btn btn-secondary" id="supplierEmbedEditCancelBtn">Cancel</button>
        @else
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        @endif
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none me-2"></span>
            Update
        </button>
    </div>
</form>

<script>
(function() {
    function updateEditGroupViewNumbersLink(container) {
        if (!container) return;
        var sel = container.querySelector('select.supplier-group-select');
        var link = container.querySelector('a.edit-supplier-group-view-numbers-link');
        if (!sel || !link) return;
        var val = sel.value;
        if (val && val !== '') {
            link.classList.remove('d-none');
            link.href = '{{ route("suppliers.group-numbers") }}?group_id=' + encodeURIComponent(val);
        } else {
            link.classList.add('d-none');
            link.href = '#';
        }
    }
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches && e.target.matches('select.supplier-group-select')) {
            updateEditGroupViewNumbersLink(e.target.closest('form'));
        }
    });
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target && e.target.id && e.target.id.indexOf('editSupplierModal') === 0) {
            var form = e.target.querySelector('form');
            if (form) updateEditGroupViewNumbersLink(form);
        }
    });
})();
</script>
<script>
(function() {
    var modalId = 'editSupplierModal{{ $supplier->id }}';
    var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    function initEditSupplierMultipleImages() {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        var form = modal.querySelector('form');
        if (!form) return;
        if (form.dataset.multiImagesInitDone === '1') return;
        form.dataset.multiImagesInitDone = '1';

        var input = form.querySelector('.supplier-edit-multiple-images-input');
        var uploadBtn = form.querySelector('.supplier-edit-upload-images-btn');
        var existingPreview = form.querySelector('.supplier-existing-images-preview');
        var newPreview = form.querySelector('.supplier-new-images-preview');
        var placeholder = form.querySelector('.upload-placeholder');
        if (!input || !uploadBtn || !newPreview) return;

        function ensureRemoveHidden(imagePath) {
            if (!imagePath) return;
            var existingInput = form.querySelector('input[name="remove_multiple_images[]"][value="' + imagePath.replace(/"/g, '\\"') + '"]');
            if (existingInput) return;
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'remove_multiple_images[]';
            hidden.value = imagePath;
            form.appendChild(hidden);
        }

        function togglePlaceholder() {
            var hasExisting = existingPreview && existingPreview.querySelector('.position-relative');
            var hasNew = newPreview.querySelector('.position-relative');
            if (placeholder) placeholder.classList.toggle('d-none', !!(hasExisting || hasNew));
        }

        function renderNewFiles(files) {
            newPreview.innerHTML = '';
            if (!files || files.length === 0) {
                newPreview.classList.add('d-none');
                newPreview.classList.remove('d-flex');
                togglePlaceholder();
                return;
            }
            newPreview.classList.remove('d-none');
            newPreview.classList.add('d-flex', 'flex-wrap', 'justify-content-center', 'gap-2', 'p-2');
            files.forEach(function(file, index) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var card = document.createElement('div');
                    card.className = 'position-relative border rounded p-2 bg-white';
                    card.style.width = '120px';
                    card.setAttribute('data-new-index', String(index));
                    card.innerHTML = '<img src="' + (ev.target.result || '').replace(/"/g, '&quot;') + '" alt="" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;">' +
                        '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-new-image" title="Remove">×</button>' +
                        '<small class="d-block text-muted mt-1 text-truncate">' + (file.name || '').replace(/</g, '&lt;') + ' (New)</small>';
                    newPreview.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
            togglePlaceholder();
        }

        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            input.click();
        });

        input.addEventListener('change', function() {
            var files = Array.from(input.files || []);
            var invalid = files.filter(function(f) { return allowedTypes.indexOf(f.type) === -1; });
            if (invalid.length > 0) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Only JPG, PNG and WebP images are allowed.');
                } else {
                    alert('Only JPG, PNG and WebP images are allowed.');
                }
                input.value = '';
                renderNewFiles([]);
                return;
            }
            renderNewFiles(files);
        });

        newPreview.addEventListener('click', function(e) {
            var btn = e.target.closest('.remove-new-image');
            if (!btn) return;
            e.preventDefault();
            var card = btn.closest('[data-new-index]');
            var idx = card ? parseInt(card.getAttribute('data-new-index'), 10) : -1;
            if (idx < 0) return;
            var files = Array.from(input.files || []);
            files.splice(idx, 1);
            var dt = new DataTransfer();
            files.forEach(function(f) { dt.items.add(f); });
            input.files = dt.files;
            renderNewFiles(files);
        });

        if (existingPreview) {
            existingPreview.addEventListener('click', function(e) {
                var btn = e.target.closest('.remove-existing-image');
                if (!btn) return;
                e.preventDefault();
                var imagePath = btn.getAttribute('data-image');
                ensureRemoveHidden(imagePath);
                var card = btn.closest('.position-relative');
                if (card) card.remove();
                togglePlaceholder();
            });
        }

        modal.addEventListener('shown.bs.modal', function() {
            togglePlaceholder();
        });

        togglePlaceholder();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditSupplierMultipleImages);
    } else {
        initEditSupplierMultipleImages();
    }

    // Fallback delegated handler: works even if modal HTML is re-rendered later.
    if (!document.body.dataset.supplierEditUploadDelegatedBound) {
        document.body.dataset.supplierEditUploadDelegatedBound = '1';
        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('.supplier-edit-upload-images-btn');
            if (!trigger) return;
            var form = trigger.closest('form');
            if (!form) return;
            var input = form.querySelector('.supplier-edit-multiple-images-input');
            if (!input) return;
            e.preventDefault();
            input.click();
        });

        // Delegated change binding ensures previews work even for dynamically re-rendered modals.
        document.addEventListener('change', function(e) {
            var input = e.target;
            if (!input || !input.classList || !input.classList.contains('supplier-edit-multiple-images-input')) return;
            var form = input.closest('form');
            if (!form) return;
            var newPreview = form.querySelector('.supplier-new-images-preview');
            var existingPreview = form.querySelector('.supplier-existing-images-preview');
            var placeholder = form.querySelector('.upload-placeholder');
            if (!newPreview) return;

            var files = Array.from(input.files || []);
            var invalid = files.filter(function(f) { return allowedTypes.indexOf(f.type) === -1; });
            if (invalid.length > 0) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Only JPG, PNG and WebP images are allowed.');
                } else {
                    alert('Only JPG, PNG and WebP images are allowed.');
                }
                input.value = '';
                files = [];
            }

            newPreview.innerHTML = '';
            if (files.length === 0) {
                newPreview.classList.add('d-none');
                newPreview.classList.remove('d-flex');
                var hasExisting = existingPreview && existingPreview.querySelector('.position-relative');
                if (placeholder) placeholder.classList.toggle('d-none', !!hasExisting);
                return;
            }

            newPreview.classList.remove('d-none');
            newPreview.classList.add('d-flex', 'flex-wrap', 'justify-content-center', 'gap-2', 'p-2');
            files.forEach(function(file, index) {
                var reader = new FileReader();
                reader.onload = function(ev) {
                    var card = document.createElement('div');
                    card.className = 'position-relative border rounded p-2 bg-white';
                    card.style.width = '120px';
                    card.setAttribute('data-new-index', String(index));
                    card.innerHTML = '<img src="' + (ev.target.result || '').replace(/"/g, '&quot;') + '" alt="" class="img-fluid rounded" style="max-height: 100px; width: 100%; object-fit: cover;">' +
                        '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-new-image" title="Remove">×</button>' +
                        '<small class="d-block text-muted mt-1 text-truncate">' + (file.name || '').replace(/</g, '&lt;') + ' (New)</small>';
                    newPreview.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
            if (placeholder) placeholder.classList.add('d-none');
        });
    }
})();
</script>















