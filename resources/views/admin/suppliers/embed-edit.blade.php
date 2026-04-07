@extends('layouts.supplier-embed')

@section('title', 'Edit supplier')

@section('content')
    @include('admin.suppliers.modals.edit-supplier-form', [
        'supplier' => $supplier,
        'groups' => $groups,
        'return_url' => null,
        'edit_supplier_id' => null,
        'embed_mode' => true,
    ])
@endsection

@push('scripts')
<script>
(function () {
    var embedStyle = document.createElement('style');
    embedStyle.textContent = '.business-detail-tag.tag-highlighted{background-color:#198754;box-shadow:0 0 0 2px rgba(25,135,84,.25);transform:translateY(-1px);}';
    document.head.appendChild(embedStyle);
    var businessDetailTags = [];
    var businessDetailCurrentSuggestions = [];
    var businessDetailSelectedIndex = -1;
    var businessDetailSuggestionTimer = null;
    var searchProductsUrl = @json(route('suppliers.products.search'));
    var storeProductUrl = @json(route('suppliers.products.store'));

    function postToParent(payload) {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage(payload, window.location.origin);
        }
    }
    function refreshNamePhoneRemoveVisibility() {
        var container = document.getElementById('namePhoneContainer');
        if (!container) return;
        var rows = container.querySelectorAll('.name-phone-row');
        rows.forEach(function (row) {
            var rm = row.querySelector('.remove-row');
            if (rm) rm.style.display = rows.length > 1 ? 'block' : 'none';
        });
    }
    document.addEventListener('click', function (e) {
        if (e.target.closest('#supplierEmbedEditCancelBtn')) {
            e.preventDefault();
            postToParent({ type: 'supplierEditEmbedClose' });
            return;
        }
        if (e.target.closest('#addNamePhone')) {
            e.preventDefault();
            var btn = e.target.closest('#addNamePhone');
            var col12 = btn.closest('.col-12');
            var container = col12 ? col12.querySelector('#namePhoneContainer') : null;
            if (!container) return;
            var row = document.createElement('div');
            row.className = 'row g-3 mb-3 align-items-end name-phone-row';
            row.innerHTML = '<div class="col-md-6">' +
                '<label class="form-label">Name <span class="text-danger">*</span></label>' +
                '<div class="input-group">' +
                '<input type="text" name="names[]" value="" class="form-control speech-input" placeholder="Enter name or use mic" required>' +
                '<button type="button" class="btn btn-outline-secondary mic-btn"><i class="fas fa-microphone"></i></button>' +
                '<button type="button" class="btn btn-danger remove-row" style="display:block;"><i class="fas fa-trash"></i></button>' +
                '</div></div>' +
                '<div class="col-md-6">' +
                '<label class="form-label">WhatsApp Number</label>' +
                '<input type="text" name="phones[]" value="" class="form-control" placeholder="Enter phone number">' +
                '</div>';
            container.appendChild(row);
            refreshNamePhoneRemoveVisibility();
            return;
        }
        if (e.target.closest('.remove-row')) {
            var row = e.target.closest('.name-phone-row');
            if (row) {
                row.remove();
                refreshNamePhoneRemoveVisibility();
            }
            return;
        }
        if (e.target.closest('#generatePassword')) {
            e.preventDefault();
            var charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            var pwd = '';
            for (var i = 0; i < 14; i++) pwd += charset.charAt(Math.floor(Math.random() * charset.length));
            var inp = document.querySelector('#supplierEditEmbedForm input[name="password"], #supplierEditEmbedForm #password');
            if (inp) {
                inp.value = pwd;
                inp.removeAttribute('readonly');
            }
            return;
        }
        if (e.target.closest('#showCreditLimitOptions')) {
            e.preventDefault();
            var def = document.getElementById('creditLimitDefault');
            var opt = document.getElementById('creditLimitOptions');
            if (def) def.style.display = 'none';
            if (opt) opt.style.display = 'block';
            var customRadio = document.querySelector('#supplierEditEmbedForm input[name="credit_limit_type"][value="custom"]');
            if (customRadio) customRadio.checked = true;
            var ci = document.getElementById('custom_limit_input');
            if (ci) ci.style.display = 'block';
            return;
        }
        if (e.target.closest('#hideCreditLimitOptions')) {
            e.preventDefault();
            var opt2 = document.getElementById('creditLimitOptions');
            var def2 = document.getElementById('creditLimitDefault');
            if (opt2) opt2.style.display = 'none';
            if (def2) def2.style.display = 'block';
            return;
        }
        if (e.target.closest('.profile-upload-box .upload-btn, .profile-upload-box .upload-placeholder, .profile-upload-box .supplier-profile-preview')) {
            e.preventDefault();
            var fileInput = document.querySelector('#supplierEditEmbedForm input[name="profile_img"]');
            if (fileInput) fileInput.click();
            return;
        }
    });
    var form = document.getElementById('supplierEditEmbedForm');
    if (!form) return;
    function updateBusinessDetailHiddenInput() {
        var hidden = form.querySelector('input[name="business_detail"]');
        if (hidden) hidden.value = JSON.stringify(businessDetailTags);
    }
    function renderBusinessDetailTags() {
        var tagsContainer = document.getElementById('business_detail_tags_edit_{{ $supplier->id }}');
        if (!tagsContainer) return;
        tagsContainer.innerHTML = businessDetailTags.map(function (tag) {
            var safeTag = String(tag || '').replace(/"/g, '&quot;');
            return '<span class="business-detail-tag">' + tag + ' <span class="tag-remove" data-tag="' + safeTag + '" title="Remove">×</span></span>';
        }).join('');
        tagsContainer.querySelectorAll('.tag-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var t = this.getAttribute('data-tag');
                businessDetailTags = businessDetailTags.filter(function (x) { return x !== t; });
                renderBusinessDetailTags();
                updateBusinessDetailHiddenInput();
            });
        });
        if (!tagsContainer._tagHighlightBound) {
            tagsContainer.addEventListener('click', function (e) {
                if (e.target.closest('.tag-remove')) return;
                var tagEl = e.target.closest('.business-detail-tag');
                if (!tagEl) return;
                tagsContainer.querySelectorAll('.business-detail-tag').forEach(function (el) {
                    if (el !== tagEl) el.classList.remove('tag-highlighted');
                });
                tagEl.classList.toggle('tag-highlighted');
            });
            tagsContainer._tagHighlightBound = true;
        }
    }
    function hasBusinessDetailTag(name) {
        var q = String(name || '').trim().toLowerCase();
        return businessDetailTags.some(function (t) { return String(t || '').toLowerCase() === q; });
    }
    function addBusinessDetailTag(name) {
        var t = String(name || '').trim();
        if (!t || hasBusinessDetailTag(t)) return;
        businessDetailTags.push(t);
        renderBusinessDetailTags();
        updateBusinessDetailHiddenInput();
    }
    function displayBusinessDetailSuggestions(list, query, suggestionsEl) {
        if (!suggestionsEl) return;
        if (!list || !list.length) {
            suggestionsEl.innerHTML = '';
            suggestionsEl.classList.remove('show');
            return;
        }
        var regex = new RegExp('(' + String(query || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
        suggestionsEl.innerHTML = list.map(function (item, i) {
            var isAdd = item.addNew === true;
            var label = isAdd ? ('Add new: ' + item.name) : item.name;
            var highlighted = String(label).replace(regex, '<span class="highlight">$1</span>');
            return '<div class="business-detail-suggestion-item' + (i === businessDetailSelectedIndex ? ' selected' : '') + '" data-value="' +
                String(item.name || '').replace(/"/g, '&quot;') + '" data-add-new="' + (isAdd ? '1' : '0') + '">' +
                '<div class="business-detail-suggestion-text">' + highlighted + '</div>' + (isAdd ? ' <span class="badge bg-primary ms-1">Add</span>' : '') + '</div>';
        }).join('');
        suggestionsEl.classList.add('show');
    }
    function navigateBusinessDetailSuggestions(direction, suggestionsEl) {
        var items = suggestionsEl ? suggestionsEl.querySelectorAll('.business-detail-suggestion-item') : [];
        if (!items.length) return;
        items.forEach(function (it) { it.classList.remove('selected'); });
        businessDetailSelectedIndex += direction;
        if (businessDetailSelectedIndex < 0) businessDetailSelectedIndex = items.length - 1;
        if (businessDetailSelectedIndex >= items.length) businessDetailSelectedIndex = 0;
        if (items[businessDetailSelectedIndex]) {
            items[businessDetailSelectedIndex].classList.add('selected');
            items[businessDetailSelectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    function addNewBusinessDetailProduct(name, inputEl, suggestionsEl) {
        var trimmed = String(name || '').trim();
        if (!trimmed) return;
        var tokenEl2 = document.querySelector('meta[name="csrf-token"]');
        var csrf = tokenEl2 ? tokenEl2.getAttribute('content') : '';
        fetch(storeProductUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'name=' + encodeURIComponent(trimmed) + '&_token=' + encodeURIComponent(csrf)
        }).then(function (r) { return r.json(); }).then(function (data) {
            addBusinessDetailTag((data && data.name) ? data.name : trimmed);
            if (inputEl) inputEl.value = '';
            if (suggestionsEl) suggestionsEl.classList.remove('show');
            businessDetailSelectedIndex = -1;
        }).catch(function () {
            addBusinessDetailTag(trimmed);
            if (inputEl) inputEl.value = '';
            if (suggestionsEl) suggestionsEl.classList.remove('show');
            businessDetailSelectedIndex = -1;
        });
    }
    function initEmbedBusinessDetailEditor() {
        var input = document.getElementById('business_detail_input_edit_{{ $supplier->id }}');
        var suggestions = document.getElementById('business_detail_suggestions_edit_{{ $supplier->id }}');
        var hidden = document.getElementById('business_detail_edit_{{ $supplier->id }}');
        if (!input || !suggestions || !hidden) return;

        try {
            var raw = (hidden.value || '').trim();
            if (raw) {
                if (raw.charAt(0) === '[') {
                    businessDetailTags = JSON.parse(raw);
                } else {
                    businessDetailTags = raw.split(',').map(function (x) { return x.trim(); }).filter(Boolean);
                }
            }
        } catch (e) {
            businessDetailTags = [];
        }
        if (!Array.isArray(businessDetailTags)) businessDetailTags = [];
        renderBusinessDetailTags();
        updateBusinessDetailHiddenInput();

        input.addEventListener('input', function () {
            var q = String(input.value || '').trim();
            if (businessDetailSuggestionTimer) clearTimeout(businessDetailSuggestionTimer);
            if (!q) {
                suggestions.classList.remove('show');
                businessDetailSelectedIndex = -1;
                return;
            }
            businessDetailSuggestionTimer = setTimeout(function () {
                fetch(searchProductsUrl + '?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var list = (data && data.products ? data.products : []).map(function (p) { return { name: p.name, addNew: false }; })
                            .filter(function (p) { return !hasBusinessDetailTag(p.name); });
                        if (!list.some(function (x) { return String(x.name || '').toLowerCase() === q.toLowerCase(); }) && !hasBusinessDetailTag(q)) {
                            list.push({ name: q, addNew: true });
                        }
                        businessDetailCurrentSuggestions = list;
                        businessDetailSelectedIndex = -1;
                        displayBusinessDetailSuggestions(list, q, suggestions);
                    }).catch(function () {
                        var fallback = hasBusinessDetailTag(q) ? [] : [{ name: q, addNew: true }];
                        businessDetailCurrentSuggestions = fallback;
                        businessDetailSelectedIndex = -1;
                        displayBusinessDetailSuggestions(fallback, q, suggestions);
                    });
            }, 180);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                var q = String(input.value || '').trim();
                if (businessDetailSelectedIndex >= 0 && businessDetailCurrentSuggestions[businessDetailSelectedIndex]) {
                    var selected = businessDetailCurrentSuggestions[businessDetailSelectedIndex];
                    if (selected.addNew) addNewBusinessDetailProduct(selected.name, input, suggestions);
                    else {
                        addBusinessDetailTag(selected.name);
                        input.value = '';
                        suggestions.classList.remove('show');
                        businessDetailSelectedIndex = -1;
                    }
                } else if (q) {
                    addNewBusinessDetailProduct(q, input, suggestions);
                }
                return false;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                navigateBusinessDetailSuggestions(1, suggestions);
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                navigateBusinessDetailSuggestions(-1, suggestions);
                return;
            }
            if (e.key === 'Escape') {
                suggestions.classList.remove('show');
                businessDetailSelectedIndex = -1;
            }
        });

        suggestions.addEventListener('click', function (e) {
            var item = e.target.closest('.business-detail-suggestion-item');
            if (!item) return;
            var value = item.getAttribute('data-value');
            var isAddNew = item.getAttribute('data-add-new') === '1';
            if (!value) return;
            if (isAddNew) addNewBusinessDetailProduct(value, input, suggestions);
            else {
                addBusinessDetailTag(value);
                input.value = '';
                suggestions.classList.remove('show');
                businessDetailSelectedIndex = -1;
            }
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.remove('show');
                businessDetailSelectedIndex = -1;
            }
        });
    }
    var tokenEl = document.querySelector('meta[name="csrf-token"]');
    var token = tokenEl ? tokenEl.getAttribute('content') : '';
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var submitBtn = form.querySelector('button[type="submit"]');
        var spin = submitBtn ? submitBtn.querySelector('.spinner-border') : null;
        if (submitBtn) submitBtn.disabled = true;
        if (spin) spin.classList.remove('d-none');
        var fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().then(function (j) {
                return { ok: res.ok, status: res.status, j: j };
            }).catch(function () {
                return { ok: false, status: res.status, j: { message: 'Invalid response' } };
            });
        }).then(function (r) {
            if (submitBtn) submitBtn.disabled = false;
            if (spin) spin.classList.add('d-none');
            if (r.ok && r.j && r.j.success) {
                postToParent({ type: 'supplierUpdatedFromPurchaseEmbed', supplier: r.j.supplier, message: r.j.message || '' });
                return;
            }
            var msg = (r.j && (r.j.message || (r.j.errors && JSON.stringify(r.j.errors)))) || 'Update failed';
            alert(msg);
        }).catch(function () {
            if (submitBtn) submitBtn.disabled = false;
            if (spin) spin.classList.add('d-none');
            alert('Network error');
        });
    });
    var profileInput = form.querySelector('input[name="profile_img"]');
    if (profileInput) {
        profileInput.addEventListener('change', function () {
            var file = profileInput.files && profileInput.files[0] ? profileInput.files[0] : null;
            var box = profileInput.closest('.profile-upload-box');
            var preview = box ? box.querySelector('.supplier-profile-preview, #profile_preview') : null;
            var placeholder = box ? box.querySelector('.upload-placeholder') : null;
            var uploadBtn = box ? box.querySelector('.upload-btn') : null;
            if (!file) {
                if (preview) {
                    preview.src = '';
                    preview.style.display = 'none';
                }
                if (placeholder) {
                    placeholder.classList.remove('d-none');
                    placeholder.style.display = '';
                }
                if (uploadBtn) uploadBtn.style.display = '';
                return;
            }
            if (!file.type || file.type.indexOf('image/') !== 0) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                if (preview) {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.classList.add('d-none');
                    placeholder.style.display = 'none';
                }
                if (uploadBtn) uploadBtn.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        refreshNamePhoneRemoveVisibility();
        initEmbedBusinessDetailEditor();
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('.supplier-group-select').select2({ width: '100%', dropdownParent: jQuery('body') });
        }
        var ad = document.getElementById('as_of_date');
        if (ad && window.jQuery && jQuery.fn.datepicker) {
            jQuery(ad).datepicker({ format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true });
        }
    });
})();
</script>
@endpush
