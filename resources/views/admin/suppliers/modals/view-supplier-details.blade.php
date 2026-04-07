@php
    $profileImg = $supplier->profile_img ? asset($supplier->profile_img) : asset('assets/img/profiles/avator1.jpg');
    $names = is_array($supplier->names ?? null) ? $supplier->names : [];
    $phones = is_array($supplier->phones ?? null) ? $supplier->phones : [];
    $businessDetail = is_array($supplier->business_detail ?? null) ? $supplier->business_detail : [];
    $emails = is_array($supplier->emails ?? null) ? $supplier->emails : [];
    if (empty($emails) && !empty($supplier->email)) {
        $emails = [$supplier->email];
    }
    $createdByUser = $supplier->createdBy ?? null;
    $createdByBranch = $supplier->createdByBranch ?? null;
    $groupName = $supplier->group ? ($supplier->group->name ?? '') : '';
    $multipleImages = is_array($supplier->multiple_images ?? null) ? $supplier->multiple_images : [];
@endphp

<div class="p-3">
    <div class="row g-3 align-items-start">
        <div class="col-md-4">
            <div class="border rounded-3 p-3 bg-white h-100">
                <div class="text-center mb-3">
                    <img src="{{ $profileImg }}" alt="Supplier Image" class="img-fluid rounded"
                        style="max-height: 220px; width: 100%; object-fit: contain;">
                </div>
                <div class="text-center">
                    <h5 class="mb-1">{{ $supplier->company ?? ($names[0] ?? 'N/A') }}</h5>
                    <div class="text-muted small">
                        {{ $names[0] ?? 'N/A' }}
                    </div>
                </div>

                <hr>

                <div class="small">
                    <div class="mb-2"><strong>Group:</strong> {{ $groupName !== '' ? $groupName : 'N/A' }}</div>
                    <div class="mb-2"><strong>Opening Balance:</strong> {{ $supplier->opening_balance ?? 0 }}</div>
                    <div class="mb-2"><strong>Balance Type:</strong> {{ ($supplier->balance_type ?? 'pay') === 'receive' ? 'To Receive' : 'To Pay' }}</div>
                    <div class="mb-0">
                        <strong>Credit Limit:</strong>
                        @if(($supplier->credit_limit_type ?? 'no_limit') === 'custom')
                            {{ $supplier->credit_limit ?? 0 }}
                        @else
                            No Limit
                        @endif
                    </div>
                    <div class="mt-2">
                        <strong>Description:</strong> <span class="text-muted">N/A (not stored)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-12">
                    <div class="bg-white border rounded-3 p-3">
                        <h6 class="mb-3">Names & Phone Numbers</h6>
                        @if(!empty($names))
                            <div class="row g-2">
                                @foreach($names as $idx => $nm)
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 bg-light">
                                            <div><strong>Name:</strong> {{ $nm }}</div>
                                            @php
                                                $rawPhone = $phones[$idx] ?? ($phones[0] ?? null);
                                                $digits = is_string($rawPhone) ? preg_replace('/\D+/', '', $rawPhone) : '';
                                                // If stored as local (starts with 0), assume Pakistan (+92) as per create flow default.
                                                if ($digits !== '' && strpos($digits, '0') === 0) {
                                                    $digits = '92' . ltrim($digits, '0');
                                                }
                                                $hasPhone = is_string($digits) && strlen($digits) >= 7;
                                                $waUrl = $hasPhone ? ('https://wa.me/' . $digits) : null;
                                                $telUrl = $hasPhone ? ('tel:' . $digits) : null;
                                            @endphp
                                            <div class="text-muted d-flex flex-wrap align-items-center gap-2">
                                                <span><strong>WhatsApp:</strong> {{ $rawPhone ?? 'N/A' }}</span>
                                                @if($hasPhone)
                                                    <div class="d-flex gap-2 ms-auto" style="align-items:center;">
                                                        <a class="btn btn-sm btn-success"
                                                           href="{{ $waUrl }}"
                                                           target="_blank"
                                                           rel="noopener"
                                                           title="Open WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                        <a class="btn btn-sm btn-warning"
                                                           href="{{ $telUrl }}"
                                                           title="Call Now">
                                                            <i class="fas fa-phone"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">N/A</div>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="bg-white border rounded-3 p-3">
                        <h6 class="mb-3">WhatsApp Numbers</h6>
                        @if(!empty($phones))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(array_values($phones) as $p)
                                    @if(!empty($p))
                                        <span class="badge bg-success rounded-pill px-3 py-2">{{ $p }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="bg-white border rounded-3 p-3">
                        <h6 class="mb-3">Products / Business Detail</h6>
                        @if(!empty($businessDetail))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($businessDetail as $d)
                                    @if(!empty($d))
                                        <span class="badge bg-primary rounded-pill px-3 py-2">{{ $d }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-white border rounded-3 p-3 h-100">
                        <h6 class="mb-3">Emails</h6>
                        @if(!empty($emails))
                            <ul class="mb-0 ps-3">
                                @foreach($emails as $em)
                                    <li>{{ $em }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="bg-white border rounded-3 p-3 h-100">
                        <h6 class="mb-3">Address & Location</h6>
                        <div class="small">
                            <div class="mb-2"><strong>Address:</strong> {{ $supplier->address ?? 'N/A' }}</div>
                            <div class="mb-0"><strong>Location:</strong> {{ $supplier->area ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="bg-white border rounded-3 p-3">
                        <h6 class="mb-3">Additional Images</h6>
                        @if(!empty($multipleImages))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($multipleImages as $img)
                                    @if(!empty($img))
                                        <div class="border rounded p-2 bg-light" style="width: 140px;">
                                            <img src="{{ asset($img) }}" class="img-fluid rounded"
                                                style="max-height: 100px; width: 100%; object-fit: cover;"
                                                onerror="this.onerror=null; this.parentElement.style.display='none';">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="bg-white border rounded-3 p-3">
                        <h6 class="mb-3">Created Info</h6>
                        <div class="small">
                            <div class="mb-2">
                                <strong>Created Date/Time:</strong>
                                @if($supplier->created_at)
                                    {{ $supplier->created_at->format('d/m/Y, h:i A') }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="mb-0">
                                <strong>Created By:</strong>
                                {{ $createdByUser ? ($createdByUser->name ?? 'N/A') : 'N/A' }}
                                @if($createdByBranch)
                                    <br><strong>Branch:</strong> {{ $createdByBranch->branch_name ?? 'N/A' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

