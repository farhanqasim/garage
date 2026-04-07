@extends('layouts.app')
@section('title', 'Group Numbers')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Group Numbers</h4>
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">Back to Suppliers</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Search Groups</h5>
                </div>
                <div class="card-body">
                    <label for="groupSearch" class="form-label small text-muted mb-1">Search groups</label>
                    <div class="mb-3">
                        <input type="text" id="groupSearch" class="form-control" placeholder="Type group name..." autocomplete="off" aria-label="Search groups by name">
                    </div>
                    <div class="mb-3">
                        <button type="button" id="addNewGroupBtn" class="btn btn-success w-100">Add New Group</button>
                    </div>
                    <div id="groupList" class="list-group" style="max-height: 70vh; overflow-y: auto;">
                        <div class="text-muted small p-2">Loading groups…</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><span id="selectedGroupName">Select a group</span></h5>
                    <span id="groupCountBadge" class="badge bg-secondary d-none">0 numbers</span>
                </div>
                <div class="card-body">
                    <div id="numbersPlaceholder" class="text-muted text-center py-5">
                        Select a group from the list to view and manage its numbers.
                    </div>
                    <div id="numbersTableWrap" class="table-responsive d-none">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th id="numbersTableGroupHeader">Group</th>
                                    <th>Country</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="numbersTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add New Group modal -->
<div class="modal fade" id="addNewGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newGroupNameInput" class="form-label">Group name</label>
                <input type="text" id="newGroupNameInput" class="form-control" placeholder="Enter group name" maxlength="255" autocomplete="off">
                <div id="addGroupError" class="invalid-feedback d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addNewGroupSaveBtn" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit number modal -->
<div class="modal fade" id="editNumberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editNumId">
                <div class="mb-2" id="editGroupNameWrap">
                    <label class="form-label">Group Name</label>
                    <input type="text" id="editGroupNameInput" class="form-control" maxlength="255" placeholder="Capital World" aria-label="Group name (editable)">
                </div>
                <div class="mb-2">
                    <label class="form-label">Country Code</label>
                    <input type="text" id="editCountryCode" class="form-control" maxlength="10" placeholder="e.g. 92">
                </div>
                <div class="mb-2">
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="editPhoneNumber" class="form-control" maxlength="50" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveEditNumBtn" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var groupList, groupSearch, selectedGroupName, groupCountBadge, numbersPlaceholder, numbersTableWrap, numbersTableBody;
    var currentGroupId = null;
    var currentGroupName = '';
    var searchDebounce = null;
    var lastSearchAbort = null;
    var isAllGroupsView = false;

    function loadGroups() {
        if (!groupSearch || !groupList) return;
        var q = (groupSearch.value || '').trim();
        if (lastSearchAbort) {
            lastSearchAbort.abort();
            lastSearchAbort = null;
        }
        var url = '{{ route("groups.index") }}' + (q ? '?q=' + encodeURIComponent(q) : '');
        groupList.innerHTML = '<div class="text-muted small p-2">Loading…</div>';
        var controller = new AbortController();
        lastSearchAbort = controller;
        fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                lastSearchAbort = null;
                var groups = data.groups || [];
                if (groups.length === 0) {
                    var safeQ = (q || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
                    groupList.innerHTML = '<div class="text-muted small p-2">' + (q ? 'No groups match &quot;' + safeQ + '&quot;.' : 'No groups found.') + '</div>';
                    return;
                }
                groupList.innerHTML = groups.map(function(g) {
                    var count = g.phone_numbers_count || 0;
                    return '<a href="#" class="list-group-item list-group-item-action group-item' + (count >= 250 ? ' list-group-item-secondary' : '') + '" data-id="' + g.id + '" data-name="' + (g.name || '').replace(/"/g, '&quot;') + '">' +
                        (g.name || '') + ' <span class="badge bg-primary rounded-pill">' + count + '</span>' + (count >= 250 ? ' <span class="badge bg-secondary">Full</span>' : '') + '</a>';
                }).join('');
                filterGroupListBySearch();
                var params = new URLSearchParams(window.location.search);
                var groupId = params.get('group_id');
                if (groupId) {
                    var item = groupList.querySelector('.group-item[data-id="' + groupId + '"]');
                    if (item) {
                        currentGroupId = item.getAttribute('data-id');
                        currentGroupName = item.getAttribute('data-name') || ('Group ' + currentGroupId);
                        if (selectedGroupName) selectedGroupName.textContent = currentGroupName;
                        groupList.querySelectorAll('.group-item').forEach(function(el) { el.classList.remove('active'); });
                        item.classList.add('active');
                        isAllGroupsView = false;
                        loadNumbers(currentGroupId);
                        return;
                    }
                }
                currentGroupId = null;
                currentGroupName = '';
                isAllGroupsView = true;
                if (selectedGroupName) selectedGroupName.textContent = 'All groups';
                loadAllGroupsNumbers();
            })
            .catch(function(err) {
                if (err && err.name === 'AbortError') return;
                lastSearchAbort = null;
                groupList.innerHTML = '<div class="text-danger small p-2">Failed to load groups.</div>';
            });
    }

    function filterGroupListBySearch() {
        if (!groupList || !groupSearch) return;
        var q = (groupSearch.value || '').trim().toLowerCase();
        var items = groupList.querySelectorAll('.group-item');
        if (items.length === 0) return;
        var visibleCount = 0;
        items.forEach(function(el) {
            var name = (el.getAttribute('data-name') || el.textContent || '').toLowerCase();
            var show = q === '' || name.indexOf(q) !== -1;
            el.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        var noMatchId = 'groupListNoMatch';
        var noMatchEl = document.getElementById(noMatchId);
        if (q && visibleCount === 0) {
            if (!noMatchEl) {
                noMatchEl = document.createElement('div');
                noMatchEl.id = noMatchId;
                noMatchEl.className = 'text-muted small p-2';
                groupList.insertBefore(noMatchEl, groupList.firstChild);
            }
            noMatchEl.textContent = 'No groups match \u201c' + (groupSearch.value || '').trim().replace(/</g, '\u200b') + '\u201d.';
            noMatchEl.style.display = '';
        } else if (noMatchEl) {
            noMatchEl.style.display = 'none';
        }
        if (q === '') {
            groupList.querySelectorAll('.group-item').forEach(function(el) { el.classList.remove('active'); });
            currentGroupId = null;
            currentGroupName = '';
            isAllGroupsView = true;
            loadAllGroupsNumbers();
        }
    }

    function runInit() {
        groupList = document.getElementById('groupList');
        groupSearch = document.getElementById('groupSearch');
        selectedGroupName = document.getElementById('selectedGroupName');
        groupCountBadge = document.getElementById('groupCountBadge');
        numbersPlaceholder = document.getElementById('numbersPlaceholder');
        numbersTableWrap = document.getElementById('numbersTableWrap');
        numbersTableBody = document.getElementById('numbersTableBody');

        if (!groupSearch || !groupList) return;

        groupSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loadGroups();
            }
        });
        groupSearch.addEventListener('input', function() {
            filterGroupListBySearch();
        });
        groupSearch.addEventListener('change', function() {
            filterGroupListBySearch();
        });

        loadGroups();

        if (groupList) {
            groupList.addEventListener('click', function(e) {
                var a = e.target.closest('.group-item');
                if (!a) return;
                e.preventDefault();
                currentGroupId = a.getAttribute('data-id');
                var name = a.getAttribute('data-name');
                currentGroupName = name || ('Group ' + currentGroupId);
                if (selectedGroupName) selectedGroupName.textContent = currentGroupName;
                groupList.querySelectorAll('.group-item').forEach(function(el) { el.classList.remove('active'); });
                a.classList.add('active');
                isAllGroupsView = false;
                loadNumbers(currentGroupId);
            });
        }
        if (numbersTableBody) {
            numbersTableBody.addEventListener('click', function(e) {
                var editBtn = e.target.closest('.edit-num-btn');
                var freezeBtn = e.target.closest('.freeze-num-btn');
                if (editBtn) {
                    document.getElementById('editNumId').value = editBtn.getAttribute('data-id');
                    document.getElementById('editCountryCode').value = editBtn.getAttribute('data-cc') || '';
                    document.getElementById('editPhoneNumber').value = editBtn.getAttribute('data-phone') || '';
                    var groupNameInput = document.getElementById('editGroupNameInput');
                    if (groupNameInput) groupNameInput.value = editBtn.getAttribute('data-group-name') || '';
                    new bootstrap.Modal(document.getElementById('editNumberModal')).show();
                }
                if (freezeBtn) {
                    var id = freezeBtn.getAttribute('data-id');
                    fetch('{{ url("groups/numbers") }}/' + id + '/freeze', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                            if (res.success) {
                                if (isAllGroupsView) loadAllGroupsNumbers();
                                else if (currentGroupId) loadNumbers(currentGroupId);
                            }
                        });
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInit);
    } else {
        runInit();
    }

    (function addNewGroupHandlers() {
        var addBtn = document.getElementById('addNewGroupBtn');
        var modalEl = document.getElementById('addNewGroupModal');
        var nameInput = document.getElementById('newGroupNameInput');
        var saveBtn = document.getElementById('addNewGroupSaveBtn');
        var errorEl = document.getElementById('addGroupError');
        if (!addBtn || !modalEl || !nameInput || !saveBtn) return;

        addBtn.addEventListener('click', function() {
            nameInput.value = '';
            if (errorEl) { errorEl.classList.add('d-none'); errorEl.textContent = ''; }
            nameInput.classList.remove('is-invalid');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(function() { nameInput.focus(); }, 200);
        });

        function hideModal() {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        saveBtn.addEventListener('click', function() {
            var name = (nameInput.value || '').trim();
            if (!name) {
                nameInput.classList.add('is-invalid');
                if (errorEl) { errorEl.textContent = 'Group name is required.'; errorEl.classList.remove('d-none'); }
                return;
            }
            if (errorEl) { errorEl.classList.add('d-none'); errorEl.textContent = ''; }
            nameInput.classList.remove('is-invalid');
            saveBtn.disabled = true;
            var token = document.querySelector('meta[name="csrf-token"]');
            token = token ? token.getAttribute('content') : '';
            fetch('{{ route("post.groups") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: '_token=' + encodeURIComponent(token) + '&name=' + encodeURIComponent(name)
            })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    saveBtn.disabled = false;
                    if (res.success && res.id) {
                        hideModal();
                        loadGroups();
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Group added.');
                        else alert(res.message || 'Group added.');
                    } else {
                        if (errorEl) { errorEl.textContent = res.message || 'Could not add group.'; errorEl.classList.remove('d-none'); }
                        nameInput.classList.add('is-invalid');
                    }
                })
                .catch(function() {
                    saveBtn.disabled = false;
                    if (errorEl) { errorEl.textContent = 'Network error.'; errorEl.classList.remove('d-none'); }
                    nameInput.classList.add('is-invalid');
                });
        });

        modalEl.addEventListener('shown.bs.modal', function() { nameInput.focus(); });
        modalEl.addEventListener('hidden.bs.modal', function() { nameInput.value = ''; if (errorEl) errorEl.classList.add('d-none'); });
    })();

    function loadAllGroupsNumbers() {
        var url = '{{ route("groups.all.numbers") }}';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var numbers = data.numbers || [];
                if (selectedGroupName) selectedGroupName.textContent = 'All groups';
                numbersPlaceholder.classList.add('d-none');
                numbersTableWrap.classList.remove('d-none');
                groupCountBadge.classList.remove('d-none');
                groupCountBadge.textContent = numbers.length + ' number(s)';
                numbersTableBody.innerHTML = numbers.map(function(n, i) {
                    var frozen = n.is_frozen;
                    return '<tr data-id="' + n.id + '">' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + (n.group_name || '—') + '</td>' +
                        '<td>' + (n.country_code || '—') + '</td>' +
                        '<td>' + (n.phone_number || '') + '</td>' +
                        '<td>' + (n.company_name || '—') + '</td>' +
                        '<td><span class="badge ' + (frozen ? 'bg-secondary' : 'bg-success') + '">' + (frozen ? 'Frozen' : 'Active') + '</span></td>' +
                        '<td><button type="button" class="btn btn-sm btn-outline-primary edit-num-btn" data-id="' + n.id + '" data-cc="' + (n.country_code || '') + '" data-phone="' + (n.phone_number || '') + '" data-company="' + (n.company_name || '') + '" data-group-name="' + (n.group_name || '').replace(/"/g, '&quot;') + '">Edit</button> ' +
                        '<button type="button" class="btn btn-sm ' + (frozen ? 'btn-warning' : 'btn-outline-secondary') + ' freeze-num-btn" data-id="' + n.id + '" data-frozen="' + (frozen ? '1' : '0') + '">' + (frozen ? 'Unfreeze' : 'Freeze') + '</button></td>' +
                        '</tr>';
                }).join('');
            })
            .catch(function() {
                numbersPlaceholder.classList.remove('d-none');
                numbersTableWrap.classList.add('d-none');
                numbersPlaceholder.textContent = 'Failed to load numbers.';
            });
    }

    function loadNumbers(groupId) {
        const url = '{{ url("groups") }}/' + groupId + '/numbers';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                const numbers = data.numbers || [];
                numbersPlaceholder.classList.add('d-none');
                numbersTableWrap.classList.remove('d-none');
                groupCountBadge.classList.remove('d-none');
                groupCountBadge.textContent = numbers.length + ' number(s)';

                numbersTableBody.innerHTML = numbers.map(function(n, i) {
                    const frozen = n.is_frozen;
                    return '<tr data-id="' + n.id + '">' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + (currentGroupName || '—') + '</td>' +
                        '<td>' + (n.country_code || '—') + '</td>' +
                        '<td>' + (n.phone_number || '') + '</td>' +
                        '<td>' + (n.company_name || '—') + '</td>' +
                        '<td><span class="badge ' + (frozen ? 'bg-secondary' : 'bg-success') + '">' + (frozen ? 'Frozen' : 'Active') + '</span></td>' +
                        '<td><button type="button" class="btn btn-sm btn-outline-primary edit-num-btn" data-id="' + n.id + '" data-cc="' + (n.country_code || '') + '" data-phone="' + (n.phone_number || '') + '" data-company="' + (n.company_name || '') + '" data-group-name="' + (currentGroupName || '').replace(/"/g, '&quot;') + '">Edit</button> ' +
                        '<button type="button" class="btn btn-sm ' + (frozen ? 'btn-warning' : 'btn-outline-secondary') + ' freeze-num-btn" data-id="' + n.id + '" data-frozen="' + (frozen ? '1' : '0') + '">' + (frozen ? 'Unfreeze' : 'Freeze') + '</button></td>' +
                        '</tr>';
                }).join('');
            })
            .catch(function() {
                numbersPlaceholder.classList.remove('d-none');
                numbersTableWrap.classList.add('d-none');
                numbersPlaceholder.textContent = 'Failed to load numbers.';
            });
    }

    document.getElementById('saveEditNumBtn').addEventListener('click', function() {
        const id = document.getElementById('editNumId').value;
        const payload = {
            phone_number: document.getElementById('editPhoneNumber').value.trim(),
            country_code: document.getElementById('editCountryCode').value.trim() || null,
            group_name: document.getElementById('editGroupNameInput').value.trim() || null
        };
        fetch('{{ url("groups/numbers") }}/' + id, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editNumberModal')).hide();
                    var payloadHadGroupName = document.getElementById('editGroupNameInput').value.trim() !== '';
                    if (payloadHadGroupName && typeof loadGroups === 'function') loadGroups();
                    if (isAllGroupsView) loadAllGroupsNumbers();
                    else if (currentGroupId) loadNumbers(currentGroupId);
                }
            });
    });
})();
</script>
@endpush
