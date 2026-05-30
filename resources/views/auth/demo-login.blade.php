@extends('layouts.auth')

@section('content')
<div class="login-box" style="width:min(96vw, 1120px)">
    <div class="card card-outline card-warning shadow-lg auth-card">
        <div class="card-body login-card-body">
            <div class="row">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="mb-2">
                        <img src="{{ $brandingLoginLogoUrl ?? asset('images/dits_logo.png') }}" alt="Daveonwork IT Solutions" style="max-width: 300px; width: 100%; height: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <span style="display:none; font-weight:700; color:#1f2f46; font-size:0.92rem;">Daveonwork IT Solutions</span>
                    </div>
                    <span class="brand-ribbon">Demo Access</span>
                    <h3 class="mt-3 mb-1">Retail Management System (RMS)</h3>
                    <p class="text-muted mb-3">Demo Login Page with quick-fill default users.</p>

                    <div class="alert alert-info mb-3">
                        <strong>How to use:</strong>
                        <ol class="mb-0 pl-3">
                            <li>Click any username from the right panel.</li>
                            <li>The username and password fields are auto-filled.</li>
                            <li>Click <strong>Sign In</strong> to continue.</li>
                        </ol>
                    </div>

                    <form action="{{ route('login.store') }}" method="post" id="demoLoginForm">
                        @csrf

                        <div class="form-group mb-2">
                            <label class="mb-1">Username or Email</label>
                            <input type="text" id="demo_login" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="Username or email" required autofocus>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">Password</label>
                            <input type="password" id="demo_password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" @checked(old('remember'))>
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block font-weight-bold">Sign In</button>
                        <a href="{{ route('login') }}?mode=normal" class="btn btn-outline-secondary btn-block mt-2">Switch to Normal Login</a>
                        <a href="{{ asset('manuals/rms-user-manual.html') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-block mt-2">Open RMS User Manual</a>
                    </form>
                </div>

                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Default Demo Users</h5>
                        <small class="text-muted">Click a row to fill credentials</small>
                    </div>

                    <div class="table-responsive" style="max-height: 510px; border: 1px solid #e9ecef; border-radius: 10px;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($demoUsers as $demoUser)
                                    <tr class="demo-user-row" data-login="{{ $demoUser['username'] ?: $demoUser['email'] }}" data-password="{{ $demoUser['password'] }}" style="cursor: pointer;">
                                        <td>{{ $demoUser['name'] }}</td>
                                        <td>
                                            <a href="#" class="demo-user-select" data-login="{{ $demoUser['username'] ?: $demoUser['email'] }}" data-password="{{ $demoUser['password'] }}">
                                                {{ $demoUser['username'] ?: $demoUser['email'] }}
                                            </a>
                                            @if ($demoUser['email'])
                                                <div class="small text-muted">{{ $demoUser['email'] }}</div>
                                            @endif
                                        </td>
                                        <td><code>{{ $demoUser['password'] }}</code></td>
                                        <td>{{ $demoUser['role'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No default users found for demo display.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const loginInput = document.getElementById('demo_login');
    const passwordInput = document.getElementById('demo_password');
    const rows = document.querySelectorAll('.demo-user-row');
    const links = document.querySelectorAll('.demo-user-select');

    function fillCredentials(login, password) {
        if (!loginInput || !passwordInput) {
            return;
        }

        loginInput.value = login || '';
        passwordInput.value = password || '';
        loginInput.focus();
    }

    rows.forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target && event.target.closest('a')) {
                return;
            }

            fillCredentials(row.dataset.login, row.dataset.password);
        });
    });

    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            fillCredentials(link.dataset.login, link.dataset.password);
        });
    });
})();
</script>
@endsection
