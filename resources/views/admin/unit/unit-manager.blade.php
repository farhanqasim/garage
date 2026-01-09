@extends('layouts.app')
@section('title', __('Unit Manager'))
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h2 class="fw-bold">Unit Manager</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <!-- Unit Selection -->
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-muted mb-2">Select Unit & Conversion:</label>
                <div class="d-flex gap-2">
                    <select id="unitSelect" onchange="handleUnitChange()" class="form-select flex-grow">
                        <option value="">-- PLEASE SELECT --</option>
                    </select>
                    <button onclick="openUnitModal('add')" class="btn btn-primary" title="Add Unit">
                        <i class="ti ti-plus"></i>
                    </button>
                    <button onclick="openUnitModal('edit')" class="btn btn-secondary" title="Edit Unit">
                        <i class="ti ti-pencil"></i>
                    </button>
                </div>
            </div>

            <!-- Warning Message -->
            <div id="priceWarning" class="alert alert-danger d-none mb-3" role="alert">
                <i class="ti ti-alert-circle me-2"></i>
                <strong>WARNING:</strong> SALE PRICE IS LESS THAN COST PRICE (LOSS)
            </div>

            <!-- Cost Section -->
            <div class="mb-4">
                <h6 class="text-uppercase fw-bold text-success mb-3">Cost Price Management</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="card border-success bg-light">
                            <div class="card-body">
                                <label id="costUnitLabel" class="form-label small fw-bold text-uppercase text-success mb-2">Unit Cost:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0">Rs.</span>
                                    <input type="number" id="costPrice" step="any" oninput="calculateFromUnit('cost')" placeholder="0" class="form-control border-0 bg-transparent fw-bold fs-5 text-success">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success bg-light">
                            <div class="card-body">
                                <label id="costBaseLabel" class="form-label small fw-bold text-uppercase text-success mb-2">Per Base Cost:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0">Rs.</span>
                                    <input type="number" id="baseCostPrice" step="any" oninput="calculateFromBase('cost')" placeholder="0" class="form-control border-0 bg-transparent fw-bold fs-5 text-success">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Section -->
            <div class="mb-4">
                <h6 class="text-uppercase fw-bold text-warning mb-3">Sale Price Management</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="card border-warning bg-light">
                            <div class="card-body">
                                <label id="saleUnitLabel" class="form-label small fw-bold text-uppercase text-warning mb-2">Unit Sale:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0">Rs.</span>
                                    <input type="number" id="salePrice" step="any" oninput="calculateFromUnit('sale')" placeholder="0" class="form-control border-0 bg-transparent fw-bold fs-5 text-warning">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning bg-light">
                            <div class="card-body">
                                <label id="saleBaseLabel" class="form-label small fw-bold text-uppercase text-warning mb-2">Per Base Sale:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0">Rs.</span>
                                    <input type="number" id="baseSalePrice" step="any" oninput="calculateFromBase('sale')" placeholder="0" class="form-control border-0 bg-transparent fw-bold fs-5 text-warning">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Prices Button -->
            <div class="mb-4">
                <button onclick="saveCurrentPrices()" id="saveEntryBtn" class="btn btn-success w-100 py-2 fw-bold text-uppercase">
                    <i class="ti ti-check me-2"></i>Save Prices for this Base
                </button>
                <p id="saveStatus" class="text-center small mt-2 text-success fw-bold d-none">PRICES SAVED FOR THIS BASE UNIT!</p>
            </div>

            <!-- Analysis View -->
            <div id="derivedPricesList" class="mt-4">
                <!-- Results injected here -->
            </div>
        </div>
    </div>
</div>

<!-- Unit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="modalTitle">Unit Settings</h5>
                <button type="button" class="btn-close" onclick="closeUnitModal()"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editIndex" value="-1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" id="newUnitName" placeholder="e.g. DRUM" class="form-control form-control-sm text-uppercase">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Short Name <span class="text-danger">*</span></label>
                        <input type="text" id="newUnitShort" placeholder="e.g. DRM" class="form-control form-control-sm text-uppercase">
                    </div>
                </div>
                <div class="row g-3 mt-2 bg-light p-3 rounded">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Allow Decimal?</label>
                        <select id="allowDecimal" onchange="toggleDecimalPrecision()" class="form-control form-control-sm">
                            <option value="0">NO</option>
                            <option value="1">YES</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="precisionContainer" style="display: none;">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Decimals</label>
                        <input type="number" id="decimalPrecision" class="form-control form-control-sm" min="0" max="10" value="2">
                    </div>
                </div>
                <div class="form-check mt-3">
                    <input type="checkbox" id="hasBase" onchange="toggleBaseSettings()" class="form-check-input">
                    <label for="hasBase" class="form-check-label small fw-bold text-uppercase">Multiple of other units</label>
                </div>
                <div id="baseSettings" class="mt-3 border-start border-4 border-warning ps-3" style="display: none;">
                    <div id="baseRows" class="space-y-3">
                        <div class="row g-2 mb-2 base-unit-row">
                            <div class="col-5">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">QTY</label>
                                <input type="number" step="any" class="form-control form-control-sm multiplier-input" placeholder="e.g., 1, 2, 3">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">BASE UNIT</label>
                                <select class="form-control form-control-sm base-unit-select">
                                    <option value="">Select Base Unit</option>
                                    @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-1 d-flex align-items-end">
                                <button onclick="removeRow(this)" class="btn btn-danger btn-sm remove-btn d-none">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button onclick="addRow()" class="btn btn-sm btn-warning text-white w-100 mt-2">
                        <i class="ti ti-plus"></i> ADD ANOTHER CONVERSION
                    </button>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button id="deleteBtn" onclick="deleteUnit()" class="btn btn-danger d-none">Delete</button>
                <button onclick="saveUnit()" class="btn btn-warning text-white fw-bold">SAVE UNIT</button>
            </div>
        </div>
    </div>
</div>

<script>
let units = @json($units);
let baseUnitRowIndex = 0;

function openUnitModal(mode) {
    const select = document.getElementById('unitSelect');
    resetForm();
    if (mode === 'edit' && select.selectedIndex > 0) {
        document.getElementById('modalTitle').innerText = "Update Unit Settings";
        document.getElementById('deleteBtn').classList.remove('d-none');
        const opt = select.selectedOptions[0];
        const unitId = opt.getAttribute('data-id');
        const unit = units.find(u => u.id == unitId);
        if (unit) {
            document.getElementById('editIndex').value = unit.id;
            document.getElementById('newUnitName').value = unit.name;
            document.getElementById('newUnitShort').value = unit.short_name;
            document.getElementById('allowDecimal').value = unit.allow_decimal == 1 ? "1" : "0";
            document.getElementById('decimalPrecision').value = unit.decimal_after_point_digit || "2";
            toggleDecimalPrecision();
            if (unit.base_units && unit.base_units.length > 0) {
                document.getElementById('hasBase').checked = true;
                toggleBaseSettings();
                const rowsContainer = document.getElementById('baseRows');
                unit.base_units.forEach((c, i) => {
                    if (i > 0) addRow();
                    const row = rowsContainer.children[i];
                    row.querySelector('.multiplier-input').value = c.multiplier;
                    row.querySelector('.base-unit-select').value = c.id;
                    if (i > 0) row.querySelector('.remove-btn').classList.remove('d-none');
                });
            }
        }
    }
    const modal = new bootstrap.Modal(document.getElementById('unitModal'));
    modal.show();
}

function closeUnitModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('unitModal'));
    if (modal) modal.hide();
}

function toggleDecimalPrecision() {
    const container = document.getElementById('precisionContainer');
    const allowDecimal = document.getElementById('allowDecimal').value;
    container.style.display = allowDecimal == '1' ? 'block' : 'none';
}

function toggleBaseSettings() {
    const container = document.getElementById('baseSettings');
    const hasBase = document.getElementById('hasBase').checked;
    container.style.display = hasBase ? 'block' : 'none';
}

function addRow() {
    const rows = document.getElementById('baseRows');
    const newRow = rows.children[0].cloneNode(true);
    newRow.querySelector('input').value = '';
    newRow.querySelector('select').value = '';
    newRow.querySelector('.remove-btn').classList.remove('d-none');
    rows.appendChild(newRow);
    updateRemoveButtons();
}

function removeRow(btn) {
    btn.closest('.base-unit-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#baseRows .base-unit-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-btn');
        if (rows.length > 1) {
            removeBtn.classList.remove('d-none');
        } else {
            removeBtn.classList.add('d-none');
        }
    });
}

function saveUnit() {
    const name = document.getElementById('newUnitName').value.trim().toUpperCase();
    const short = document.getElementById('newUnitShort').value.trim().toUpperCase();
    const editId = document.getElementById('editIndex').value;
    
    if (!name || !short) {
        alert("Please fill name and short name");
        return;
    }
    
    let baseUnits = [];
    if (document.getElementById('hasBase').checked) {
        document.querySelectorAll('#baseRows .base-unit-row').forEach(row => {
            const m = row.querySelector('.multiplier-input').value;
            const b = row.querySelector('.base-unit-select').value;
            if (m && b) {
                baseUnits.push({ multiplier: m, base_unit_id: b });
            }
        });
    }
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('short_name', short);
    formData.append('allow_decimal', document.getElementById('allowDecimal').value);
    formData.append('decimal_after_point_digit', document.getElementById('decimalPrecision').value || 2);
    formData.append('is_base_unit', document.getElementById('hasBase').checked ? 1 : 0);
    baseUnits.forEach((bu, index) => {
        formData.append(`base_units[${index}][multiplier]`, bu.multiplier);
        formData.append(`base_units[${index}][base_unit_id]`, bu.base_unit_id);
    });
    
    const url = editId !== "-1" ? `/admin/units/${editId}` : '/admin/units';
    const method = editId !== "-1" ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function renderUnits() {
    const select = document.getElementById('unitSelect');
    const currentVal = select.value;
    select.innerHTML = '<option value="">-- PLEASE SELECT --</option>';
    
    units.forEach(u => {
        if (u.base_units && u.base_units.length > 0) {
            u.base_units.forEach((c, index) => {
                const opt = new Option(`1 ${u.name} = ${c.multiplier} ${c.name}`, `${u.id}-${c.id}`);
                opt.setAttribute('data-id', u.id);
                opt.setAttribute('data-original-name', u.name);
                opt.setAttribute('data-short', u.short_name);
                opt.setAttribute('data-decimal', u.decimal_after_point_digit);
                opt.setAttribute('data-conversions', JSON.stringify(u.base_units));
                opt.setAttribute('data-active-conv-index', index);
                select.appendChild(opt);
            });
        } else {
            const opt = new Option(`1 ${u.name}`, u.id);
            opt.setAttribute('data-id', u.id);
            opt.setAttribute('data-original-name', u.name);
            opt.setAttribute('data-short', u.short_name);
            opt.setAttribute('data-decimal', u.decimal_after_point_digit);
            opt.setAttribute('data-conversions', JSON.stringify([]));
            opt.setAttribute('data-active-conv-index', -1);
            select.appendChild(opt);
        }
    });
    select.value = currentVal;
}

function handleUnitChange() {
    const opt = document.getElementById('unitSelect').selectedOptions[0];
    if (opt && opt.value) {
        const name = opt.getAttribute('data-original-name');
        const convs = JSON.parse(opt.getAttribute('data-conversions') || '[]');
        const activeIdx = parseInt(opt.getAttribute('data-active-conv-index'));
        const baseName = (convs.length > 0 && activeIdx !== -1) ? convs[activeIdx].name : name;
        
        document.getElementById('costUnitLabel').innerText = `${name} COST:`;
        document.getElementById('costBaseLabel').innerText = `PER ${baseName} COST:`;
        document.getElementById('saleUnitLabel').innerText = `${name} SALE:`;
        document.getElementById('saleBaseLabel').innerText = `PER ${baseName} SALE:`;
    }
    syncPrices();
}

function calculateFromUnit(type) {
    const opt = document.getElementById('unitSelect').selectedOptions[0];
    if (!opt || opt.value === "") return;
    const convs = JSON.parse(opt.getAttribute('data-conversions') || '[]');
    const activeIdx = parseInt(opt.getAttribute('data-active-conv-index'));
    
    const unitInput = document.getElementById(type === 'cost' ? 'costPrice' : 'salePrice');
    const baseInput = document.getElementById(type === 'cost' ? 'baseCostPrice' : 'baseSalePrice');
    const unitVal = parseFloat(unitInput.value) || 0;

    if (convs.length > 0 && activeIdx !== -1) {
        const multiplier = parseFloat(convs[activeIdx].multiplier) || 1;
        baseInput.value = (unitVal / multiplier).toFixed(2);
    } else {
        baseInput.value = unitVal.toFixed(2);
    }
    syncPrices();
}

function calculateFromBase(type) {
    const opt = document.getElementById('unitSelect').selectedOptions[0];
    if (!opt || opt.value === "") return;
    const convs = JSON.parse(opt.getAttribute('data-conversions') || '[]');
    const activeIdx = parseInt(opt.getAttribute('data-active-conv-index'));
    
    const unitInput = document.getElementById(type === 'cost' ? 'costPrice' : 'salePrice');
    const baseInput = document.getElementById(type === 'cost' ? 'baseCostPrice' : 'baseSalePrice');
    const baseVal = parseFloat(baseInput.value) || 0;

    if (convs.length > 0 && activeIdx !== -1) {
        const multiplier = parseFloat(convs[activeIdx].multiplier) || 1;
        unitInput.value = (baseVal * multiplier).toFixed(2);
    } else {
        unitInput.value = baseVal.toFixed(2);
    }
    syncPrices();
}

function syncPrices() {
    const opt = document.getElementById('unitSelect').selectedOptions[0];
    const salePrice = parseFloat(document.getElementById('salePrice').value) || 0;
    const costPrice = parseFloat(document.getElementById('costPrice').value) || 0;
    const list = document.getElementById('derivedPricesList');
    const warning = document.getElementById('priceWarning');
    
    list.innerHTML = '';
    
    if (salePrice > 0 && costPrice > 0 && salePrice < costPrice) {
        warning.classList.remove('d-none');
    } else {
        warning.classList.add('d-none');
    }
    
    if (!opt || opt.value === "" || (salePrice === 0 && costPrice === 0)) return;
    
    const unitName = opt.getAttribute('data-original-name');
    const convs = JSON.parse(opt.getAttribute('data-conversions') || '[]');
    const activeIdx = parseInt(opt.getAttribute('data-active-conv-index'));
    const decimal = 2;
    const totalProfit = salePrice - costPrice;
    const totalMargin = salePrice > 0 ? (totalProfit / salePrice * 100).toFixed(1) : 0;
    const isTotalLoss = totalProfit < 0;

    list.innerHTML += `
        <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold text-uppercase">FULL UNIT ANALYSIS: ${unitName}</span>
                <span class="badge ${isTotalLoss ? 'bg-danger' : 'bg-success'}">${isTotalLoss ? 'LOSS' : 'Margin'}: ${totalMargin}%</span>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <p class="small text-muted mb-1">TOTAL COST</p>
                        <p class="fw-bold text-success fs-5">Rs.${costPrice.toFixed(decimal)}</p>
                    </div>
                    <div class="col-4">
                        <p class="small text-muted mb-1">TOTAL SALE</p>
                        <p class="fw-bold text-warning fs-5">Rs.${salePrice.toFixed(decimal)}</p>
                    </div>
                    <div class="col-4">
                        <p class="small text-muted mb-1">TOTAL PROFIT</p>
                        <p class="fw-bold ${totalProfit >= 0 ? 'text-primary' : 'text-danger'} fs-5">Rs.${totalProfit.toFixed(decimal)}</p>
                    </div>
                </div>
            </div>
        </div>`;

    if (activeIdx !== -1 && convs[activeIdx]) {
        const c = convs[activeIdx];
        const baseSale = (salePrice / c.multiplier);
        const baseCost = (costPrice / c.multiplier);
        const profit = baseSale - baseCost;
        const margin = baseSale > 0 ? (profit / baseSale * 100).toFixed(1) : 0;
        const isLoss = profit < 0;
        list.innerHTML += `
            <div class="card border-secondary mb-3 ${isLoss ? 'border-danger' : ''}">
                <div class="card-header ${isLoss ? 'bg-danger text-white' : 'bg-secondary text-white'} d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-uppercase">Analysis per ${c.name}</span>
                    <span class="badge ${isLoss ? 'bg-dark' : 'bg-success'}">${isLoss ? 'LOSS' : 'Margin'}: ${margin}%</span>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <p class="small text-muted mb-1">COST/${c.name}</p>
                            <p class="fw-bold text-success">Rs.${baseCost.toFixed(decimal)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">SALE/${c.name}</p>
                            <p class="fw-bold text-warning">Rs.${baseSale.toFixed(decimal)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">PROFIT/${c.name}</p>
                            <p class="fw-bold ${profit >= 0 ? 'text-primary' : 'text-danger'}">Rs.${profit.toFixed(decimal)}</p>
                        </div>
                    </div>
                </div>
            </div>`;
    }
}

function saveCurrentPrices() {
    const opt = document.getElementById('unitSelect').selectedOptions[0];
    if (!opt || opt.value === "") {
        alert("Please select a unit first");
        return;
    }
    const status = document.getElementById('saveStatus');
    status.classList.remove('d-none');
    setTimeout(() => { status.classList.add('d-none'); }, 3000);
}

function resetForm() {
    document.getElementById('newUnitName').value = '';
    document.getElementById('newUnitShort').value = '';
    document.getElementById('editIndex').value = "-1";
    document.getElementById('hasBase').checked = false;
    document.getElementById('allowDecimal').value = "0";
    document.getElementById('deleteBtn').classList.add('d-none');
    toggleDecimalPrecision();
    toggleBaseSettings();
    const rows = document.getElementById('baseRows');
    while (rows.children.length > 1) rows.removeChild(rows.lastChild);
    rows.children[0].querySelector('input').value = '';
    rows.children[0].querySelector('select').value = '';
}

function deleteUnit() {
    const id = document.getElementById('editIndex').value;
    if (id !== "-1" && confirm("Delete this unit?")) {
        fetch(`/admin/units/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderUnits();
});
</script>
@endsection

