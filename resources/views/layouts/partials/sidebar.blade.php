@php
    $modules = config('rms.modules', []);
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard.owner') }}" class="brand-link">
        <i class="fas fa-store-alt ml-3 mr-2"></i>
        <span class="brand-text font-weight-light">RC Store RMS</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                @if (auth()->user()?->hasPermission('dashboard.owner.view'))
                    <li class="nav-item">
                        <a href="{{ route('dashboard.owner') }}" class="nav-link {{ request()->routeIs('dashboard.owner') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endif

                @foreach ($modules as $module)
                    @continue(! auth()->user()?->hasPermission($module['permission']))
                    <li class="nav-item">
                        <a href="{{ route($module['route']) }}" class="nav-link {{ request()->routeIs($module['route']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>{{ $module['name'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
