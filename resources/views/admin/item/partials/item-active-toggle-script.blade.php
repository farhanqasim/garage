{{-- Shared: Activate/Deactivate from item listing Actions dropdowns (POST items/{id}/toggle-active) --}}
@php
    $itemToggleActiveUrlTemplate = str_replace('/0/toggle-active', '/__ITEM_ID__/toggle-active', route('items.toggle_active', ['id' => 0]));
@endphp
<script>
(function() {
    function itemToggleActiveUrl(id) {
        return @json($itemToggleActiveUrlTemplate).replace('__ITEM_ID__', encodeURIComponent(id));
    }
    function updateItemRowAfterToggle(itemId, isActive) {
        var $row = $('tr[data-item-id="' + itemId + '"]');
        if (!$row.length) return;
        $row.find('.js-item-status-badge').removeClass('bg-success bg-secondary bg-danger')
            .addClass(isActive ? 'bg-success' : 'bg-secondary')
            .text(isActive ? 'Active' : 'Inactive');
        $row.find('.js-item-status-badge-toggle').data('is-active', isActive ? 1 : 0)
            .attr('data-is-active', isActive ? 1 : 0);
        var $a = $row.find('a.js-item-toggle-active');
        if ($a.length) {
            $a.data('is-active', isActive ? 1 : 0);
            $a.attr('data-is-active', isActive ? 1 : 0);
            if (isActive) {
                $a.html('<i data-feather="toggle-right" class="me-1"></i> Deactivate');
            } else {
                $a.html('<i data-feather="toggle-left" class="me-1"></i> Activate');
            }
        }
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    $(document).on('click', 'a.js-item-toggle-active, .js-item-status-badge-toggle', function(e) {
        e.preventDefault();
        var $el = $(this);
        var itemId = $el.data('item-id');
        if (!itemId) return;
        var currentlyActive = $el.data('is-active') == 1 || String($el.attr('data-is-active')) === '1';
        var willDeactivate = currentlyActive;
        var title = willDeactivate ? 'Deactivate item?' : 'Activate item?';
        var text = willDeactivate
            ? 'Are you sure you want to deactivate this item?'
            : 'Are you sure you want to activate this item?';
        var run = function() {
            var token = $('meta[name="csrf-token"]').attr('content');
            fetch(itemToggleActiveUrl(itemId), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || ''
                },
                body: '{}'
            }).then(function(r) {
                return r.json().then(function(d) {
                    return { ok: r.ok, d: d };
                }).catch(function() {
                    return { ok: r.ok, d: {} };
                });
            }).then(function(p) {
                if (!p.ok || !p.d.success) {
                    var msg = (p.d && p.d.message) ? p.d.message : 'Could not update item status.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else {
                        alert(msg);
                    }
                    return;
                }
                var ia = !!p.d.is_active;
                if (typeof toastr !== 'undefined') {
                    toastr.success(p.d.message || (ia ? 'Item activated.' : 'Item deactivated.'));
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Done',
                        text: p.d.message || '',
                        timer: 1600,
                        showConfirmButton: false
                    });
                }
                updateItemRowAfterToggle(itemId, ia);
                try {
                    document.dispatchEvent(new CustomEvent('item-is-active-toggled', {
                        detail: { itemId: itemId, isActive: ia }
                    }));
                } catch (err) { /* ignore */ }
            }).catch(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('Network error.');
                }
            });
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(r) {
                if (r.isConfirmed) run();
            });
        } else if (confirm(text)) {
            run();
        }
    });
    $(document).on('keydown', '.js-item-status-badge-toggle', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });
})();
</script>
