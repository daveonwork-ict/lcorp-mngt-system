@extends('layouts.app')

@section('page_title', 'Settings')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.owner') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Branding Assets</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Upload and manage logos used by login pages, sidebar, and browser tab icon.</p>

                <form action="{{ route('settings.branding.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold">Login Logo</label>
                        <div class="d-flex align-items-center mb-2" style="gap: 12px;">
                            <img src="{{ $brandingLoginLogoUrl }}" alt="Login Logo" style="max-height: 68px; max-width: 250px; width: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 6px; background: #fff;">
                            <div class="small text-muted">Used on normal and demo login pages.</div>
                        </div>
                        <input type="file" name="login_logo" class="form-control-file @error('login_logo') is-invalid @enderror">
                        @error('login_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="clear_login_logo" name="clear_login_logo">
                            <label class="form-check-label" for="clear_login_logo">Clear custom login logo and use default</label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label class="font-weight-bold">Sidenav Logo (White Version)</label>
                        <div class="d-flex align-items-center mb-2" style="gap: 12px;">
                            <div style="background:#1f2d3d; border-radius:8px; padding:8px 10px; display:inline-flex;">
                                <img src="{{ $brandingSidenavLogoUrl }}" alt="Sidenav Logo" style="max-height: 36px; max-width: 180px; width: auto; object-fit: contain;">
                            </div>
                            <div class="small text-muted">Recommended transparent white logo for dark sidebar.</div>
                        </div>
                        <input type="file" name="sidenav_logo" class="form-control-file @error('sidenav_logo') is-invalid @enderror">
                        @error('sidenav_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="clear_sidenav_logo" name="clear_sidenav_logo">
                            <label class="form-check-label" for="clear_sidenav_logo">Clear custom sidenav logo and use default</label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Favicon / Browser Tab Icon</label>
                        <div class="small text-muted mb-2">Optional icon for browser tabs. Recommended square PNG or ICO.</div>
                        <input type="file" name="favicon_logo" class="form-control-file @error('favicon_logo') is-invalid @enderror">
                        @error('favicon_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="clear_favicon_logo" name="clear_favicon_logo">
                            <label class="form-check-label" for="clear_favicon_logo">Clear custom favicon</label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Branding Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><strong>Tips</strong></div>
            <div class="card-body small text-muted">
                <p class="mb-2">Use PNG with transparent background for best results.</p>
                <p class="mb-2">Sidenav logo works best in white/light tone because the sidebar background is dark.</p>
                <p class="mb-0">After saving, refresh open pages to see updates immediately.</p>
            </div>
        </div>
    </div>
</div>
@endsection
