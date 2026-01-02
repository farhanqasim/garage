@extends('layouts.app')
@section('title', __('Settings'))
@section('content')
<div class="content">
    <section class="section">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title"> Update Setting</h5>
                        </div>
                        <form action="{{ route('admin.setting.save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    @foreach ($settings as $setting)
                                        @if ($setting->type == 'text')
                                            <div class="form-group">
                                                <label for="name" class="form-label fw-bold">{{ $setting->label }}</label>
                                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                    class="form-control">
                                                    @if ($setting->note)
                                                        <small>Note: {{$setting->note}}</small>
                                                    @endif
                                            </div>
                                        @elseif ($setting->type == 'file')
                                            <div class="form-group">
                                                <label for="name" class="form-label fw-bold">{{ $setting->label }}</label>
                                                <input type="file" name="{{ $setting->key }}" class="form-control">
                                                @if ($setting->note)
                                                    <small>Note: {{$setting->note}}</small>
                                                @endif
                                                @if (setting_value($setting->key))
                                                    @if ($setting->key === 'logo_with_text')
                                                        <img src="{{ setting_value($setting->key) }}" width="160"
                                                            height="50" style="margin-top: 24px">
                                                    @elseif ($setting->key === 'pages_top_banner')
                                                        <img src="{{ setting_value($setting->key) }}"
                                                            height="50" style="margin-top: 24px">
                                                    @else
                                                    {{-- @if ($setting->key === 'logo') --}}
                                                        <img src="{{ setting_value($setting->key) }}" width="50"
                                                            height="50" style="margin-top: 24px">
                                                    @endif
                                                @endif
                                            </div>
                                        @elseif($setting->type == 'number')
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ $setting->label }}</label>
                                                <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                    class="form-control">
                                                    @if ($setting->note)
                                                        <small>Note: {{$setting->note}}</small>
                                                    @endif
                                            </div>
                                        @elseif($setting->type == 'select')
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ $setting->label }}</label>
                                                <select name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                    class="form-control">
                                                    @foreach ($setting->options ?? [] as $value)
                                                        <option value="{{ $value }}"
                                                            @if ($setting->value == $value) selected @endif>
                                                            {{ ucwords($value) }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($setting->note)
                                                    <small>Note: {{$setting->note}}</small>
                                                @endif
                                            </div>
                                        @elseif($setting->type == 'textarea')
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ $setting->label }}</label>
                                                <textarea name="{{ $setting->key }}" rows="5" cols="20" class="form-control">{{ $setting->value }}</textarea>
                                                @if ($setting->note)
                                                    <small>Note: {{$setting->note}}</small>
                                                @endif
                                            </div>
                                        @elseif($setting->type == 'textarea_editor')
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ $setting->label }}</label>
                                                <textarea name="{{ $setting->key }}" rows="5" cols="20" class="form-control summernote" required>{{ $setting->value }}</textarea>
                                                @if ($setting->note)
                                                    <small>Note: {{$setting->note}}</small>
                                                @endif
                                            </div>
                                        @else
                                            <div class="form-group">
                                                <label for="name" class="form-label fw-bold">{{ $setting->label }}</label>
                                                <input type="{{$setting->type}}" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                    class="form-control">
                                                    @if ($setting->note)
                                                        <small>Note: {{$setting->note}}</small>
                                                    @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-lg btn-primary">Update</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
