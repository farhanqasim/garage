@extends('layouts.app')
@section('title', 'Sales')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Sales</h4>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>
        </ul>
        <div class="page-btn">
            <a href="{{ route('create.sale') }}" class="btn btn-primary" ><i
                    class="ti ti-circle-plus me-1"></i>Add Sales</a>
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Customer
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Carl Evans</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Minerva Rameriz</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Robert Lamon</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Patricia Lewis</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Staus
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Completed</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Payment Status
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Sort By : Last 7 Days
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Grand Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                            <th>Biller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="sales-list">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                        <img src="{{ asset('assets/img/users/user-27.jpg') }}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Carl Evans</a>
                                </div>
                            </td>
                            <td>SL001</td>
                            <td>24 Dec 2024</td>
                            <td><span class="badge badge-success">Completed</span></td>
                            <td>$1000</td>
                            <td>$1000</td>
                            <td>$0.00</td>
                            <td><span class="badge badge-soft-success shadow-none badge-xs"><i
                                        class="ti ti-point-filled me-1"></i>Paid</span></td>
                            <td>Admin</td>
                            <td class="text-center no-highlight">
                                <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown"
                                    aria-expanded="true">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#sales-details-new"><i data-feather="eye"
                                                class="info-img"></i>Sale Detail</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#edit-sales-new"><i data-feather="edit"
                                                class="info-img"></i>Edit Sale</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#showpayment"><i data-feather="dollar-sign"
                                                class="info-img"></i>Show Payments</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#createpayment"><i data-feather="plus-circle"
                                                class="info-img"></i>Create Payment</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download"
                                                class="info-img"></i>Download pdf</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal"
                                            data-bs-target="#delete"><i data-feather="trash-2"
                                                class="info-img"></i>Delete Sale</a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer -->
<div class="modal fade" id="add_customer">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div class="page-title">
                    <h4>Add Customer</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="customerForm" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Phone -->
                    <div>
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="number" id="phone" class="form-control">
                    </div>

                    <!-- Customer Name + Mic -->
                    <div class="mt-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>

                        <div class="input-group">
                            <input type="text" id="customerName" class="form-control">

                            <button type="button" id="startRecord" class="btn btn-outline-primary">
                                <i class="fa fa-microphone"></i>
                            </button>

                            <button type="button" id="stopRecord" class="btn btn-outline-danger d-none">
                                <i class="fa fa-stop"></i>
                            </button>
                        </div>

                        <small id="recordingStatus" class="text-primary d-none">Recording… speak now</small>

                        <div id="audioPreview" class="mt-2 d-none">
                            <audio controls id="recordedAudio" class="w-100"></audio>
                            <button type="button" id="deleteAudio" class="btn btn-sm btn-danger mt-2">Delete
                                Recording</button>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script>
    let mediaRecorder;
    let audioChunks = [];
    let finalAudioBlob = null;
    let recognition = new webkitSpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = true;
    recognition.onresult = (e) => {
        const text = e.results[0][0].transcript;
        document.getElementById("customerName").value = text;
    };
    document.getElementById("startRecord").onclick = async () => {
        document.getElementById("recordingStatus").classList.remove("d-none");
        document.getElementById("stopRecord").classList.remove("d-none");
        document.getElementById("startRecord").classList.add("d-none");
        const stream = await navigator.mediaDevices.getUserMedia({
            audio: true
        });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
            finalAudioBlob = new Blob(audioChunks, {
                type: "audio/webm"
            });
            const audioURL = URL.createObjectURL(finalAudioBlob);
            document.getElementById("recordedAudio").src = audioURL;
            document.getElementById("audioPreview").classList.remove("d-none");
            document.getElementById("recordingStatus").classList.add("d-none");
        };
        mediaRecorder.start();
        recognition.start();
    };
    document.getElementById("stopRecord").onclick = () => {
        mediaRecorder.stop();
        recognition.stop();
        document.getElementById("stopRecord").classList.add("d-none");
        document.getElementById("startRecord").classList.remove("d-none");
    };
    // Delete recording
    document.getElementById("deleteAudio").onclick = () => {
        finalAudioBlob = null;
        document.getElementById("audioPreview").classList.add("d-none");
        document.getElementById("customerName").value = "";
    };
</script>


@endsection


