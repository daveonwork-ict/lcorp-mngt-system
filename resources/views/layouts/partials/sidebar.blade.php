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

                <li class="nav-header">ACCESS CONTROL</li>
                @if (auth()->user()?->hasPermission('view_users'))
                    <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Users</p></a></li>
                @endif
                @if (auth()->user()?->hasPermission('view_roles'))
                    <li class="nav-item"><a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-tag"></i><p>Roles</p></a></li>
                @endif
                @if (auth()->user()?->hasPermission('assign_permissions'))
                    <li class="nav-item"><a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"><i class="nav-icon fas fa-key"></i><p>Permissions</p></a></li>
                @endif
                @if (auth()->user()?->hasPermission('view_branches'))
                    <li class="nav-item"><a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="nav-icon fas fa-code-branch"></i><p>Branch Management</p></a></li>
                @endif
                @if (auth()->user()?->hasPermission('view_audit_logs'))
                    <li class="nav-item"><a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.security.*') ? 'active' : '' }}"><i class="nav-icon fas fa-shield-alt"></i><p>Security Logs</p></a></li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
