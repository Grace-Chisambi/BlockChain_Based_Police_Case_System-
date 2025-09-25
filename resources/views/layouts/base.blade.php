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
            margin: 0;
            padding: 0;
            font-family: 'Heebo', sans-serif;
            background-color: #f5f5f5; /* Light gray background */
        }

        .sidebar {
            width: 272px;
            background: #fff; /* White sidebar */
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
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.6); /* Blue glow */
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

    <!-- Main Content -->
    <div class="content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand px-4 py-2">
            <a href="#" class="sidebar-toggler flex-shrink-0"><i class="fa fa-bars"></i></a>

            <div class="navbar-nav align-items-center ms-auto d-flex">
                <!-- User Profile -->
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                        <img class="rounded-circle me-lg-2" src="{{ asset('img/user.jpg') }}" alt="User" />
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
