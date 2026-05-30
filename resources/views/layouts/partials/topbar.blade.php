<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item mr-2 d-none d-md-block">@include('layouts.partials.branch-switcher')</li>
        <li class="nav-item">@include('layouts.partials.notification-bell')</li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#"><i class="far fa-user"></i></a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item-text">{{ auth()->user()?->name }}</span>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="far fa-id-badge mr-2"></i>My Profile
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST" class="px-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-block">Logout</button>
                </form>
            </div>
        </li>
    </ul>
</nav>
