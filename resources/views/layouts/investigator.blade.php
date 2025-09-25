<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BPCM System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('img/favicon.ico') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries -->
    <link href="{{ asset('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Bootstrap & Template Styles -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Heebo', sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 272px;
            background: #fff;
            position: fixed;
            top: 0;
            height: 100vh;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content {
            margin-left: 261px;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar .navbar-nav .nav-link:hover {
            background-color: #0d6efd;
            color: #ffffff;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.6);
            transition: all 0.2s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
<div class="container-xxl">
    <!-- Sidebar -->
    <div class="sidebar pe-4 pb-3">
        <nav class="navbar navbar-light flex-column align-items-start w-100">
            <a href="{{ url('admin') }}" class="navbar-brand mx-4 mb-3">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 200px; height: 100px;">
            </a>
            <div class="navbar-nav w-100">
                <a href="{{ url('investigator/dash') }}" class="nav-item nav-link active">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                 <a href="{{ route('complaints.review') }}" class="nav-item nav-link">
                    <i class="fas fa-clipboard-check me-2"></i>Review Complaints
                </a>
                <a href="{{ url('investigator/assign') }}" class="nav-item nav-link">
                    <i class="fas fa-briefcase me-2"></i>Assigned Cases
                </a>
                 <a href="{{ url('suspects') }}"class="nav-item nav-link">
                    <i class="fas fa-clipboard-check me-2"></i>Review Suspects
                </a>
                <a href="{{ url('investigator/progress_form') }}" class="nav-item nav-link">
                    <i class="fas fa-tasks me-2"></i>Case Progress
                </a>
                <a href="{{ url('investigator/case_closures') }}" class="nav-item nav-link">
                    <i class="fas fa-folder-minus me-2"></i>Close Case
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand px-4 py-2">
            <a href="#" class="sidebar-toggler flex-shrink-0"><i class="fa fa-bars"></i></a>

            <div class="navbar-nav align-items-center ms-auto d-flex">

                <!-- Notification Bell -->
                <div class="nav-item dropdown me-3">
                    <a href="#" class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-lg"></i>
                        @php
                            $unreadCount = Auth::user()->unreadNotifications->count();
                        @endphp
                        @if ($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="width: 300px; max-height: 350px; overflow-y: auto;">
                        <h6 class="dropdown-header">Notifications</h6>
                        @forelse (Auth::user()->notifications->take(5) as $notification)
                            <div class="dropdown-item small">
                                <strong>{{ $notification->data['message'] ?? 'Notification' }}</strong><br>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                @if (!$notification->read_at)
                                    <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}" class="mt-1">
                                        @csrf
                                        <button class="btn btn-sm btn-link p-0 text-success">Mark as Read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="dropdown-divider"></div>
                        @empty
                            <div class="dropdown-item text-muted">No notifications</div>
                        @endforelse
                        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center text-primary mt-2">
                            View All Notifications
                        </a>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                        <span class="d-none d-lg-inline-flex">{{ Auth::user()->fname }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ url('profile/edit') }}" class="dropdown-item">My Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        @yield('content')

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('lib/chart/chart.min.js') }}"></script>
<script src="{{ asset('lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>

<!-- Sidebar Toggle -->
<script>
    $(document).ready(function () {
        $('.sidebar-toggler').click(function () {
            $('.sidebar').toggleClass('active');
            $('.content').toggleClass('collapsed');
        });
    });
</script>
</body>
</html>
