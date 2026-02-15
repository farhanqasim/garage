@extends('layouts.app')
@section('title', 'Task Reminder')
@push('styles')
<style>
  .sales-banner-btn:hover .sales-banner-inner { opacity: 0.95; transform: translateY(-1px); }
  .sales-banner-btn .sales-banner-inner { transition: opacity 0.2s ease, transform 0.2s ease; }
</style>
@endpush
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div class="page-title">
            <h4 class="fw-bold mb-1">Task Reminder</h4>
            <p class="text-muted mb-0 small">Manage your task reminders and track updates.</p>
        </div>
    </div>

    {{-- Purchase-style banner: Task Reminder / New Task --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <a href="javascript:void(0)" class="d-flex flex-fill text-decoration-none sales-banner-btn" data-bs-toggle="modal" data-bs-target="#addTaskModal" style="cursor: pointer;">
                <div class="sales-banner-inner card flex-fill border-0 overflow-hidden sale-widget" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="card-body d-flex align-items-center text-white">
                        <span class="bg-white rounded-2 d-flex align-items-center justify-content-center p-3 me-3" style="color: #d97706;">
                            <i class="ti ti-bell fs-24"></i>
                        </span>
                        <div>
                            <p class="text-white mb-1">Task Reminder</p>
                            <div class="d-inline-flex align-items-center flex-wrap gap-2">
                                <span class="text-white fw-semibold">New Task</span>
                                <i class="ti ti-arrow-right fs-18 opacity-90"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-clock fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Pending</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $pendingCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-check fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Completed</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $tasks->where('status', 'Completed')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-list fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Total Tasks</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $tasks->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Task list --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold">Tasks</h5>
        </div>
        <div class="card-body">
            @if($tasks->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-bell-off fs-48 mb-3 d-block opacity-50"></i>
                    <p class="mb-2">No tasks yet.</p>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">Create first task</button>
                </div>
            @else
                <div class="row g-3">
                    @foreach($tasks as $task)
                        <div class="col-md-6 col-xl-4">
                            <div class="card border shadow-sm h-100 task-card" style="cursor: pointer;" data-task-id="{{ $task->id }}" data-task="{{ json_encode($task) }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-{{ $task->priority === 'Critical' ? 'danger' : ($task->priority === 'High' ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $task->priority === 'Critical' ? 'danger' : ($task->priority === 'High' ? 'warning' : 'secondary') }} text-uppercase small">{{ $task->priority }}</span>
                                        <span class="badge bg-{{ $task->status === 'Completed' ? 'success' : ($task->status === 'In-Progress' ? 'info' : 'warning') }} bg-opacity-10 text-{{ $task->status === 'Completed' ? 'success' : ($task->status === 'In-Progress' ? 'info' : 'warning') }} text-uppercase small">{{ $task->status }}</span>
                                    </div>
                                    <h6 class="card-title fw-bold text-dark mb-2">{{ $task->title }}</h6>
                                    <p class="small text-muted mb-2 line-clamp-2" style="--lines: 2;">{{ $task->description ?: '—' }}</p>
                                    <div class="small text-muted d-flex justify-content-between">
                                        <span>{{ $task->branch->branch_name ?? '—' }}</span>
                                        <span>{{ $task->assignee ?: '—' }}</span>
                                    </div>
                                    @if(!empty($task->responses))
                                        <p class="small mb-0 mt-2 text-primary"><i class="ti ti-message me-1"></i>{{ count($task->responses) }} update(s)</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Task Modal --}}
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fw-bold">New Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm" method="post" action="{{ route('task.reminder.store') }}">
                <div class="modal-body" style="pointer-events: auto;">
                    @csrf
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <label class="form-label fw-semibold mb-0">Title</label>
                            <div class="d-flex align-items-center gap-2">
                                <span id="addTaskVoicePreview" class="small text-success d-none"><i class="ti ti-microphone me-1"></i>Voice added</span>
                                <button type="button" id="addTaskVoiceBtn" class="btn btn-sm btn-outline-primary" title="Record voice message" onclick="if(window.taskReminderStartVoice) window.taskReminderStartVoice(); else alert('Please refresh the page and try again.'); return false;">
                                    <i class="ti ti-microphone"></i> Voice
                                </button>
                                <button type="button" id="addTaskVoiceStopBtn" class="btn btn-sm btn-danger d-none" disabled title="Stop recording" onclick="if(window.taskReminderStopVoice) window.taskReminderStopVoice(); return false;"><i class="ti ti-square"></i> Stop</button>
                            </div>
                        </div>
                        <input type="text" name="title" class="form-control" placeholder="Task name" required>
                        <input type="hidden" name="task_audio" id="addTaskAudioInput" value="">
                        <div id="addTaskAudioPlayback" class="mt-2 d-none">
                            <audio id="addTaskAudioPlayer" controls class="w-100" style="height: 36px;"></audio>
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-1" id="addTaskVoiceRemove" onclick="if(window.taskReminderClearVoice) window.taskReminderClearVoice(); return false;">Remove voice</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Instructions..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Picture</label>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="file" id="addTaskImageInput" class="form-control form-control-sm" accept="image/*" style="max-width: 220px;">
                            <input type="hidden" name="task_image" id="addTaskImageData" value="">
                        </div>
                        <div id="addTaskImagePreview" class="mt-2 d-none">
                            <img id="addTaskImageImg" src="" alt="Preview" class="img-fluid rounded border" style="max-height: 160px;">
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-1" id="addTaskImageRemove">Remove picture</button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (isset($defaultBranchId) && $defaultBranchId == $b->id) ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Assignee</label>
                            <select name="assignee" class="form-select">
                                <option value="">— Select worker —</option>
                                @foreach($workers ?? [] as $worker)
                                    <option value="{{ $worker->name }}">
                                        {{ $worker->name }}{{ $worker->branch ? ' (' . $worker->branch->branch_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Normal" selected>Normal</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Task Detail Modal --}}
<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <div>
                    <h5 class="modal-title fw-bold" id="detailTaskTitle">—</h5>
                    <p class="small text-muted mb-0" id="detailTaskMeta">—</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailTaskVoiceWrap" class="mb-4 d-none">
                    <p class="text-muted small text-uppercase fw-semibold mb-1"><i class="ti ti-microphone me-1"></i> Voice message</p>
                    <audio id="detailTaskAudioPlayer" controls class="w-100" style="height: 40px;"></audio>
                </div>
                <div id="detailTaskImageWrap" class="mb-4 d-none">
                    <p class="text-muted small text-uppercase fw-semibold mb-1"><i class="ti ti-photo me-1"></i> Picture</p>
                    <img id="detailTaskImage" src="" alt="Task" class="img-fluid rounded border" style="max-height: 280px;">
                </div>
                <div class="mb-4">
                    <p class="text-muted small text-uppercase fw-semibold mb-1">Instructions</p>
                    <p id="detailTaskDescription" class="mb-0">—</p>
                </div>
                <hr>
                <p class="small fw-semibold text-uppercase text-muted mb-2"><i class="ti ti-message me-1"></i> Field Updates</p>
                <div id="detailTaskResponses" class="mb-4" style="max-height: 280px; overflow-y: auto;">
                    <p class="text-muted small mb-0">No updates yet.</p>
                </div>
                <div class="border-top pt-3">
                    <div id="replyPreviews" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="file" accept="image/*" id="replyPhotoInput" class="d-none">
                        <button type="button" id="replyPhotoBtn" class="btn btn-light btn-sm" title="Attach photo"><i class="ti ti-camera"></i></button>
                        <button type="button" id="replyLocationBtn" class="btn btn-light btn-sm" title="Share location"><i class="ti ti-map-pin"></i></button>
                        <button type="button" id="replyMicBtn" class="btn btn-light btn-sm" title="Voice note"><i class="ti ti-microphone"></i></button>
                        <input type="text" id="responseText" class="form-control flex-grow-1" style="min-width: 120px;" placeholder="Write update...">
                        <button type="button" id="sendResponseBtn" class="btn btn-primary">Send</button>
                        <button type="button" id="completeTaskBtn" class="btn btn-success">Mark Complete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Standalone voice recording: runs first so it always works --}}
<script>
(function() {
    var addTaskMR = null, addTaskChunks = [], addTaskRecording = false;
    function addTaskClearVoice() {
        var el = function(id) { return document.getElementById(id); };
        if (el('addTaskAudioInput')) el('addTaskAudioInput').value = '';
        if (el('addTaskVoicePreview')) el('addTaskVoicePreview').classList.add('d-none');
        if (el('addTaskAudioPlayback')) el('addTaskAudioPlayback').classList.add('d-none');
        if (el('addTaskVoiceBtn')) { el('addTaskVoiceBtn').classList.remove('d-none'); el('addTaskVoiceBtn').disabled = false; }
        if (el('addTaskVoiceStopBtn')) { el('addTaskVoiceStopBtn').classList.add('d-none'); el('addTaskVoiceStopBtn').disabled = true; }
        if (el('addTaskAudioPlayer')) el('addTaskAudioPlayer').src = '';
    }
    function addTaskStartVoice() {
        var btn = document.getElementById('addTaskVoiceBtn'), stopBtn = document.getElementById('addTaskVoiceStopBtn');
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { alert('Microphone not supported.'); return; }
        if (typeof MediaRecorder === 'undefined') { alert('Voice recording not supported in this browser.'); return; }
        if (btn) { btn.classList.add('d-none'); btn.disabled = true; }
        if (stopBtn) { stopBtn.classList.remove('d-none'); stopBtn.disabled = false; }
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
            addTaskChunks = [];
            try { addTaskMR = new MediaRecorder(stream); } catch (e) { addTaskMR = new MediaRecorder(stream); }
            addTaskMR.ondataavailable = function(e) { if (e.data && e.data.size) addTaskChunks.push(e.data); };
            addTaskMR.onstop = function() {
                addTaskRecording = false;
                if (btn) { btn.classList.remove('d-none'); btn.disabled = false; }
                if (stopBtn) { stopBtn.classList.add('d-none'); stopBtn.disabled = true; }
                stream.getTracks().forEach(function(t) { t.stop(); });
                if (addTaskChunks.length === 0) { alert('No audio. Speak for 1–2 seconds then tap Stop.'); return; }
                var blob = new Blob(addTaskChunks, { type: addTaskMR.mimeType || 'audio/webm' });
                var r = new FileReader();
                r.onloadend = function() {
                    var i = document.getElementById('addTaskAudioInput'), p = document.getElementById('addTaskVoicePreview'), w = document.getElementById('addTaskAudioPlayback'), a = document.getElementById('addTaskAudioPlayer');
                    if (i) i.value = r.result; if (p) p.classList.remove('d-none'); if (w) w.classList.remove('d-none'); if (a) a.src = r.result;
                };
                r.readAsDataURL(blob);
            };
            addTaskMR.start(200);
            addTaskRecording = true;
        }).catch(function() { alert('Allow microphone access when the browser asks.'); if (btn) { btn.classList.remove('d-none'); btn.disabled = false; } if (stopBtn) { stopBtn.classList.add('d-none'); stopBtn.disabled = true; } });
    }
    function addTaskStopVoice() { if (addTaskRecording && addTaskMR) addTaskMR.stop(); }
    window.taskReminderStartVoice = addTaskStartVoice;
    window.taskReminderStopVoice = addTaskStopVoice;
    window.taskReminderClearVoice = addTaskClearVoice;
    var m = document.getElementById('addTaskModal');
    if (m) m.addEventListener('show.bs.modal', addTaskClearVoice);
})();
</script>
<script>
(function() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrf = meta ? meta.getAttribute('content') : '';
    var detailModal = document.getElementById('taskDetailModal');
    var currentTaskId = null;
    var replyPhoto = null;
    var replyAudio = null;
    var replyLocation = null;
    var mediaRecorder = null;
    var audioChunks = [];
    var isRecording = false;
    var addTaskMediaRecorder = null;
    var addTaskAudioChunks = [];
    var addTaskIsRecording = false;

    function post(url, data) {
        var body = new FormData();
        body.append('_token', csrf);
        for (var k in data) if (data.hasOwnProperty(k) && data[k] != null && data[k] !== '') body.append(k, data[k]);
        return fetch(url, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    }

    function renderResponse(r) {
        var html = '<div class="border rounded p-3 mb-2">';
        if (r.text) html += '<p class="small mb-2">' + (r.text || '').replace(/</g, '&lt;') + '</p>';
        if (r.photo) html += '<img src="' + r.photo + '" alt="Photo" class="img-fluid rounded mb-2" style="max-height: 200px;">';
        if (r.audio) html += '<div class="mb-2"><audio src="' + r.audio + '" controls class="w-100" style="height: 32px;"></audio></div>';
        if (r.location && r.location.lat != null) {
            var mapUrl = 'https://www.google.com/maps?q=' + r.location.lat + ',' + r.location.lng;
            html += '<a href="' + mapUrl + '" target="_blank" rel="noopener" class="d-inline-flex align-items-center small text-success"><i class="ti ti-map-pin me-1"></i>View on map</a>';
        }
        html += '<p class="small text-muted mb-0">' + (r.user_name || '') + ' • ' + (r.created_at ? new Date(r.created_at).toLocaleString() : '') + '</p></div>';
        return html;
    }

    function updateReplyPreviews() {
        var wrap = document.getElementById('replyPreviews');
        if (!wrap) return;
        wrap.innerHTML = '';
        if (replyPhoto) {
            var d = document.createElement('div');
            d.className = 'position-relative d-inline-block';
            d.innerHTML = '<img src="' + replyPhoto + '" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;"><button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0" style="width:18px;height:18px;font-size:10px;line-height:1;" data-clear="photo">&times;</button>';
            d.querySelector('[data-clear="photo"]').onclick = function() { replyPhoto = null; updateReplyPreviews(); };
            wrap.appendChild(d);
        }
        if (replyLocation) {
            var loc = document.createElement('span');
            loc.className = 'badge bg-success bg-opacity-10 text-success';
            loc.innerHTML = '<i class="ti ti-map-pin me-1"></i>Location <button type="button" class="btn btn-link p-0 ms-1 text-success" style="font-size:10px;" data-clear="loc">&times;</button>';
            loc.querySelector('[data-clear="loc"]').onclick = function() { replyLocation = null; updateReplyPreviews(); };
            wrap.appendChild(loc);
        }
        if (replyAudio) {
            var aud = document.createElement('span');
            aud.className = 'badge bg-primary bg-opacity-10 text-primary';
            aud.innerHTML = '<i class="ti ti-microphone me-1"></i>Voice <button type="button" class="btn btn-link p-0 ms-1 text-primary" style="font-size:10px;" data-clear="audio">&times;</button>';
            aud.querySelector('[data-clear="audio"]').onclick = function() { replyAudio = null; updateReplyPreviews(); };
            wrap.appendChild(aud);
        }
    }

    function clearAddTaskVoice() {
        var inp = document.getElementById('addTaskAudioInput');
        var prev = document.getElementById('addTaskVoicePreview');
        var playWrap = document.getElementById('addTaskAudioPlayback');
        var btn = document.getElementById('addTaskVoiceBtn');
        var stopBtn = document.getElementById('addTaskVoiceStopBtn');
        var player = document.getElementById('addTaskAudioPlayer');
        if (inp) inp.value = '';
        if (prev) prev.classList.add('d-none');
        if (playWrap) playWrap.classList.add('d-none');
        if (btn) { btn.classList.remove('d-none'); btn.disabled = false; }
        if (stopBtn) { stopBtn.classList.add('d-none'); stopBtn.disabled = true; }
        if (player) player.src = '';
    }

    function startAddTaskVoiceRecording() {
        var voiceBtn = document.getElementById('addTaskVoiceBtn');
        var stopBtn = document.getElementById('addTaskVoiceStopBtn');
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Microphone not supported. Use Chrome or Edge on HTTPS or localhost.');
                return;
            }
            if (typeof MediaRecorder === 'undefined') {
                alert('Voice recording not supported in this browser. Use Chrome or Edge.');
                return;
            }
            if (voiceBtn) { voiceBtn.classList.add('d-none'); voiceBtn.disabled = true; }
            if (stopBtn) { stopBtn.classList.remove('d-none'); stopBtn.disabled = false; }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
                addTaskAudioChunks = [];
                var options = {};
                if (typeof MediaRecorder.isTypeSupported === 'function' && MediaRecorder.isTypeSupported('audio/webm')) options.mimeType = 'audio/webm';
                try {
                    addTaskMediaRecorder = new MediaRecorder(stream, options);
                } catch (err) {
                    addTaskMediaRecorder = new MediaRecorder(stream);
                }
                addTaskMediaRecorder.ondataavailable = function(ev) { if (ev.data && ev.data.size > 0) addTaskAudioChunks.push(ev.data); };
                addTaskMediaRecorder.onstop = function() {
                    addTaskIsRecording = false;
                    if (voiceBtn) { voiceBtn.classList.remove('d-none'); voiceBtn.disabled = false; }
                    if (stopBtn) { stopBtn.classList.add('d-none'); stopBtn.disabled = true; }
                    stream.getTracks().forEach(function(t) { t.stop(); });
                    if (addTaskAudioChunks.length === 0) {
                        alert('No audio recorded. Try again and speak for a second or two.');
                        return;
                    }
                    var blob = new Blob(addTaskAudioChunks, { type: addTaskMediaRecorder.mimeType || 'audio/webm' });
                    var reader = new FileReader();
                    reader.onloadend = function() {
                        var inp = document.getElementById('addTaskAudioInput');
                        var prev = document.getElementById('addTaskVoicePreview');
                        var playWrap = document.getElementById('addTaskAudioPlayback');
                        var player = document.getElementById('addTaskAudioPlayer');
                        if (inp) inp.value = reader.result;
                        if (prev) prev.classList.remove('d-none');
                        if (playWrap) playWrap.classList.remove('d-none');
                        if (player) player.src = reader.result;
                    };
                    reader.readAsDataURL(blob);
                };
                addTaskMediaRecorder.onerror = function(ev) {
                    addTaskIsRecording = false;
                    if (voiceBtn) { voiceBtn.classList.remove('d-none'); voiceBtn.disabled = false; }
                    if (stopBtn) stopBtn.classList.add('d-none');
                    stream.getTracks().forEach(function(t) { t.stop(); });
                    alert('Recording error. Try again.');
                };
                addTaskMediaRecorder.start();
                addTaskIsRecording = true;
            }).catch(function(err) {
                if (voiceBtn) { voiceBtn.classList.remove('d-none'); voiceBtn.disabled = false; }
                if (stopBtn) stopBtn.classList.add('d-none');
                console.error('Voice record error', err);
                alert('Could not access microphone. Use HTTPS or localhost and allow microphone permission when prompted.');
            });
        } catch (e) {
            console.error('Voice start error', e);
            alert('Voice error: ' + (e.message || 'Unknown error'));
            if (voiceBtn) { voiceBtn.classList.remove('d-none'); voiceBtn.disabled = false; }
            if (stopBtn) { stopBtn.classList.add('d-none'); stopBtn.disabled = true; }
        }
    }

    function stopAddTaskVoiceRecording() {
        if (addTaskIsRecording && addTaskMediaRecorder) addTaskMediaRecorder.stop();
    }

    if (typeof window.taskReminderStartVoice === 'undefined') {
        window.taskReminderStartVoice = startAddTaskVoiceRecording;
        window.taskReminderStopVoice = stopAddTaskVoiceRecording;
        window.taskReminderClearVoice = clearAddTaskVoice;
    }

    (function attachVoiceButtonClicks() {
        var modal = document.getElementById('addTaskModal');
        var handler = function(e) {
            var voiceBtn = e.target.closest('#addTaskVoiceBtn');
            var stopBtn = e.target.closest('#addTaskVoiceStopBtn');
            var removeBtn = e.target.closest('#addTaskVoiceRemove');
            if (voiceBtn) { e.preventDefault(); e.stopPropagation(); startAddTaskVoiceRecording(); return; }
            if (stopBtn) { e.preventDefault(); e.stopPropagation(); if (window.taskReminderStopVoice) window.taskReminderStopVoice(); return; }
            if (removeBtn) { e.preventDefault(); e.stopPropagation(); clearAddTaskVoice(); return; }
        };
        if (modal) modal.addEventListener('click', handler);
        else document.addEventListener('DOMContentLoaded', function() { var m = document.getElementById('addTaskModal'); if (m) m.addEventListener('click', handler); });
    })();

    function clearAddTaskImage() {
        var inp = document.getElementById('addTaskImageInput');
        var dataEl = document.getElementById('addTaskImageData');
        var prev = document.getElementById('addTaskImagePreview');
        var img = document.getElementById('addTaskImageImg');
        if (inp) inp.value = '';
        if (dataEl) dataEl.value = '';
        if (prev) prev.classList.add('d-none');
        if (img) img.src = '';
    }
    var addTaskImageInputEl = document.getElementById('addTaskImageInput');
    if (addTaskImageInputEl) {
        addTaskImageInputEl.addEventListener('change', function() {
            var f = this.files && this.files[0];
            var dataEl = document.getElementById('addTaskImageData');
            var prev = document.getElementById('addTaskImagePreview');
            var img = document.getElementById('addTaskImageImg');
            if (!f || !dataEl || !prev || !img) return;
            if (!f.type.match(/^image\//)) { alert('Please select an image file.'); this.value = ''; return; }
            var r = new FileReader();
            r.onloadend = function() {
                dataEl.value = r.result || '';
                img.src = r.result || '';
                prev.classList.remove('d-none');
            };
            r.readAsDataURL(f);
        });
    }
    var addTaskImageRemoveEl = document.getElementById('addTaskImageRemove');
    if (addTaskImageRemoveEl) addTaskImageRemoveEl.addEventListener('click', function() { clearAddTaskImage(); });
    var addTaskModalEl = document.getElementById('addTaskModal');
    if (addTaskModalEl) {
        addTaskModalEl.addEventListener('show.bs.modal', function() { clearAddTaskVoice(); clearAddTaskImage(); });
        addTaskModalEl.addEventListener('shown.bs.modal', function() {
            var titleInput = document.querySelector('#addTaskForm input[name="title"]');
            if (titleInput) { titleInput.focus(); titleInput.select && titleInput.select(); }
        });
    }

    var addTaskFormEl = document.getElementById('addTaskForm');
    if (addTaskFormEl) addTaskFormEl.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        var fd = new FormData(form);
        var tokenInput = form.querySelector('input[name="_token"]');
        var token = tokenInput ? tokenInput.value : (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        if (token) fd.set('_token', token);
        var storeUrl = '{{ route("task.reminder.store") }}';
        fetch(storeUrl, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function(r) {
                if (r.status === 419) {
                    return { ok: false, status: 419, data: { message: 'Session expired. Please refresh the page (F5) and try again.' } };
                }
                var ct = (r.headers.get('Content-Type') || '');
                if (ct.indexOf('application/json') !== -1) {
                    return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; }).catch(function() { return { ok: false, status: r.status, data: { message: 'Invalid server response.' } }; });
                }
                return r.text().then(function(t) { return { ok: false, status: r.status, data: { message: (r.status === 500 ? 'Server error. Try again or remove voice and save.' : t && t.length < 200 ? t : 'Request failed (' + r.status + ').') } }; });
            })
            .catch(function(err) {
                return { ok: false, status: 0, data: { message: 'Network error. Check connection and try again.' } };
            })
            .then(function(result) {
                if (result.ok && result.data && result.data.success) {
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    var msg = (result.data && result.data.message) || (result.data && result.data.errors && Object.values(result.data.errors).flat().join(' ')) || 'Could not create task. Please try again.';
                    alert(msg);
                    if (result.status === 419) window.location.reload();
                }
            });
    });

    document.querySelectorAll('.task-card').forEach(function(el) {
        el.addEventListener('click', function() {
            var task = JSON.parse(el.getAttribute('data-task'));
            currentTaskId = task.id;
            replyPhoto = null; replyAudio = null; replyLocation = null;
            updateReplyPreviews();
            document.getElementById('detailTaskTitle').textContent = task.title;
            document.getElementById('detailTaskMeta').textContent = (task.branch && task.branch.branch_name ? task.branch.branch_name : '—') + ' • ' + (task.assignee || '—');
            document.getElementById('detailTaskDescription').textContent = task.description || '—';
            var voiceWrap = document.getElementById('detailTaskVoiceWrap');
            var voicePlayer = document.getElementById('detailTaskAudioPlayer');
            if (task.task_audio && voiceWrap && voicePlayer) {
                voiceWrap.classList.remove('d-none');
                voicePlayer.src = task.task_audio;
            } else if (voiceWrap) {
                voiceWrap.classList.add('d-none');
                if (voicePlayer) voicePlayer.src = '';
            }
            var imageWrap = document.getElementById('detailTaskImageWrap');
            var detailImage = document.getElementById('detailTaskImage');
            if (task.task_image && imageWrap && detailImage) {
                imageWrap.classList.remove('d-none');
                detailImage.src = task.task_image;
            } else if (imageWrap) {
                imageWrap.classList.add('d-none');
                if (detailImage) detailImage.src = '';
            }
            var resp = task.responses || [];
            var box = document.getElementById('detailTaskResponses');
            if (resp.length === 0) {
                box.innerHTML = '<p class="text-muted small mb-0">No updates yet.</p>';
            } else {
                box.innerHTML = resp.map(renderResponse).join('');
            }
            document.getElementById('responseText').value = '';
            document.getElementById('completeTaskBtn').style.display = task.status === 'Completed' ? 'none' : 'inline-block';
            new bootstrap.Modal(detailModal).show();
        });
    });

    document.getElementById('replyPhotoBtn').addEventListener('click', function() { document.getElementById('replyPhotoInput').click(); });
    document.getElementById('replyPhotoInput').addEventListener('change', function(e) {
        var f = e.target.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function() { replyPhoto = r.result; updateReplyPreviews(); };
        r.readAsDataURL(f);
        e.target.value = '';
    });

    document.getElementById('replyLocationBtn').addEventListener('click', function() {
        if (!navigator.geolocation) { alert('Location not supported'); return; }
        navigator.geolocation.getCurrentPosition(function(p) {
            replyLocation = { lat: p.coords.latitude, lng: p.coords.longitude };
            updateReplyPreviews();
        }, function() { alert('Could not get location'); });
    });

    document.getElementById('replyMicBtn').addEventListener('click', function() {
        if (isRecording && mediaRecorder) {
            mediaRecorder.stop();
            return;
        }
        if (replyAudio) { replyAudio = null; updateReplyPreviews(); return; }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { alert('Microphone not supported'); return; }
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            mediaRecorder.ondataavailable = function(ev) { if (ev.data.size) audioChunks.push(ev.data); };
            mediaRecorder.onstop = function() {
                isRecording = false;
                document.getElementById('replyMicBtn').classList.remove('text-danger');
                document.getElementById('replyMicBtn').innerHTML = '<i class="ti ti-microphone"></i>';
                var blob = new Blob(audioChunks, { type: 'audio/webm' });
                var reader = new FileReader();
                reader.onloadend = function() { replyAudio = reader.result; updateReplyPreviews(); };
                reader.readAsDataURL(blob);
                stream.getTracks().forEach(function(t) { t.stop(); });
            };
            mediaRecorder.start();
            isRecording = true;
            document.getElementById('replyMicBtn').classList.add('text-danger');
            document.getElementById('replyMicBtn').innerHTML = '<i class="ti ti-square"></i> Stop';
        }).catch(function() { alert('Microphone access denied'); });
    });

    document.getElementById('sendResponseBtn').addEventListener('click', function() {
        if (!currentTaskId) return;
        var text = document.getElementById('responseText').value.trim();
        if (!text && !replyPhoto && !replyAudio && !replyLocation) return;
        var payload = { text: text };
        if (replyPhoto) payload.photo = replyPhoto;
        if (replyAudio) payload.audio = replyAudio;
        if (replyLocation) payload.location = JSON.stringify(replyLocation);
        replyPhoto = null; replyAudio = null; replyLocation = null;
        updateReplyPreviews();
        document.getElementById('responseText').value = '';
        post('{{ url("task-reminder") }}/' + currentTaskId + '/response', payload)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.task && data.task.responses) {
                    var box = document.getElementById('detailTaskResponses');
                    box.innerHTML = data.task.responses.map(renderResponse).join('');
                }
                window.location.reload();
            });
    });

    document.getElementById('completeTaskBtn').addEventListener('click', function() {
        if (!currentTaskId) return;
        post('{{ url("task-reminder") }}/' + currentTaskId + '/complete', {})
            .then(function(r) { return r.json(); })
            .then(function() {
                bootstrap.Modal.getInstance(detailModal).hide();
                window.location.reload();
            });
    });
})();
</script>
@endpush
