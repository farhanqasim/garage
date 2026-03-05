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

            <!-- Company -->
            <div class="col-md-6">
                <label for="company" class="form-label">Company</label>
                <input type="text" name="company" value="{{ old('company') }}" class="form-control" style="text-transform: uppercase;" maxlength="255" oninput="this.value=this.value.toUpperCase()">
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
                <div class="input-group">
                    <select name="group_id" class="form-select supplier-group-select" style="border-radius: 6px 0 0 6px;">
                        <option value="">Select Group</option>
                        @foreach($groups ?? [] as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary open-universal-modal" style="border-radius: 0 6px 6px 0;" title="Edit group" data-mode="edit" data-title="Edit Group" data-fetch-route="{{ route('show.groups', ':id') }}" data-update-route="{{ route('post.groups.update', ':id') }}" data-delete-route="{{ route('post.groups.destroy', ':id') }}" data-target-select=".supplier-group-select"><i class="ti ti-edit"></i></button>
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
                <input type="text" name="password" id="password" value="" class="form-control" readonly placeholder="Click Generate" required>
                <div class="mt-2">
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
            inputField.style.removeProperty('color');
            inputField.style.removeProperty('backgroundColor');
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
                var inputGroup = nameCol.querySelector('.input-group');
                var inputField = inputGroup ? inputGroup.querySelector('input[type="text"]') : null;
                var mb2 = nameCol.querySelector('.mb-2');
                var controlBtn = mb2 ? (mb2.querySelector('.mic-btn') || mb2.querySelector('.play-pause-btn') || mb2.querySelector('button')) : null;
                audioContainer.remove();
                if (inputField) {
                    inputField.readOnly = false;
                    inputField.removeAttribute('readonly');
                    inputField.style.backgroundColor = '';
                    inputField.style.cursor = '';
                }
                var form = getForm();
                if (form) {
                    var vn = form.querySelector('input[name="voice_note"]');
                    if (vn) vn.remove();
                }
                if (inputField && controlBtn && nameCol) resetSupplierModalRecordingUI(inputField, controlBtn, nameCol);
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

        if (e.target.id === 'generatePassword') {
            var charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            var password = '';
            for (var i = 0; i < 14; i++) password += charset.charAt(Math.floor(Math.random() * charset.length));
            var passInput = modal.querySelector('#password');
            if (passInput) passInput.value = password;
            return;
        }

        if (e.target.closest('#addNamePhone')) {
            var container = modal.querySelector('#namePhoneContainer');
            if (!container) return;
            var row = document.createElement('div');
            row.className = 'row g-3 mb-3 name-phone-row';
            row.innerHTML = '<div class="col-md-6"><label class="form-label">Record Voice NAME <span class="text-danger">*</span></label><div class="mb-2"><button type="button" class="btn btn-outline-secondary mic-btn w-100"><i class="fas fa-microphone me-2"></i>Record Voice</button></div><div class="input-group"><input type="text" name="names[]" class="form-control speech-input" placeholder="Enter name or use mic"></div></div><div class="col-md-6"><label class="form-label">WhatsApp Number</label><div class="mb-2"><select name="country_codes[]" class="form-select phone-country-code w-100" style="max-width: 200px;"><option value="1">🇺🇸 +1 (US/CA)</option><option value="44">🇬🇧 +44 (UK)</option><option value="91">🇮🇳 +91 (India)</option><option value="92" selected>🇵🇰 +92 (Pakistan)</option><option value="971">🇦🇪 +971 (UAE)</option><option value="966">🇸🇦 +966 (Saudi)</option><option value="974">🇶🇦 +974 (Qatar)</option><option value="965">🇰🇼 +965 (Kuwait)</option><option value="973">🇧🇭 +973 (Bahrain)</option><option value="968">🇴🇲 +968 (Oman)</option><option value="961">🇱🇧 +961 (Lebanon)</option><option value="20">🇪🇬 +20 (Egypt)</option><option value="27">🇿🇦 +27 (South Africa)</option><option value="49">🇩🇪 +49 (Germany)</option><option value="33">🇫🇷 +33 (France)</option><option value="39">🇮🇹 +39 (Italy)</option><option value="34">🇪🇸 +34 (Spain)</option><option value="31">🇳🇱 +31 (Netherlands)</option><option value="32">🇧🇪 +32 (Belgium)</option><option value="41">🇨🇭 +41 (Switzerland)</option><option value="43">🇦🇹 +43 (Austria)</option><option value="86">🇨🇳 +86 (China)</option><option value="81">🇯🇵 +81 (Japan)</option><option value="82">🇰🇷 +82 (South Korea)</option><option value="65">🇸🇬 +65 (Singapore)</option><option value="60">🇲🇾 +60 (Malaysia)</option><option value="62">🇮🇩 +62 (Indonesia)</option><option value="66">🇹🇭 +66 (Thailand)</option><option value="84">🇻🇳 +84 (Vietnam)</option><option value="63">🇵🇭 +63 (Philippines)</option><option value="880">🇧🇩 +880 (Bangladesh)</option><option value="94">🇱🇰 +94 (Sri Lanka)</option><option value="95">🇲🇲 +95 (Myanmar)</option><option value="977">🇳🇵 +977 (Nepal)</option></select></div><div class="input-group"><input type="text" name="phones[]" class="form-control phone-number-input" placeholder="Enter phone number"><button type="button" class="btn btn-success phone-whatsapp-btn" title="Open WhatsApp"><i class="fab fa-whatsapp"></i></button><button type="button" class="btn btn-primary phone-call-btn" title="Call"><i class="fas fa-phone"></i></button></div><div class="mt-2"><button type="button" class="btn btn-danger remove-row w-100"><i class="fas fa-trash me-2"></i>Remove</button></div></div>';
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

















