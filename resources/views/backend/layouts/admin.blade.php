<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 0.5rem;
        }
        .sidebar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .stat-card {
            border-left: 4px solid;
        }
        .stat-card.primary {
            border-left-color: #3b82f6;
        }
        .stat-card.success {
            border-left-color: #10b981;
        }
        .stat-card.warning {
            border-left-color: #f59e0b;
        }
        .stat-card.info {
            border-left-color: #06b6d4;
        }
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 col-lg-2 d-md-block sidebar">
                <div class="sidebar-brand">
                    <i class="bi bi-speedometer2"></i> Admin Panel
                </div>
                <ul class="nav flex-column mt-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('backend.dashboard') ? 'active' : '' }}" href="{{ route('backend.dashboard') }}">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('backend.events.*') ? 'active' : '' }}" href="{{ route('backend.events.index') }}">
                            <i class="bi bi-calendar-event"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('backend.bookings.*') ? 'active' : '' }}" href="{{ route('backend.bookings.index') }}">
                            <i class="bi bi-ticket-perforated"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('backend.users.*') ? 'active' : '' }}" href="{{ route('backend.users.index') }}">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bi bi-arrow-left-circle"></i> Back to Site
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-10 ms-sm-auto col-lg-10 px-0 main-content">
                <div class="topbar d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
                    <div>
                        <span class="text-muted me-3">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>

                <div class="container-fluid px-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
