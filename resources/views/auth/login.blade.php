<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | BPCM SySteM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Dashmin CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Sign In Start -->
    <div class="container-fluid">
        <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3 shadow">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo" style="width: 200px; height: 100px;">
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i>BPCMS</h3>
                       
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="Email" value="{{ old('email') }}" required autofocus>
                            <label for="email">Email address</label>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-floating mb-4">
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Password" required>
                            <label for="password">Password</label>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot Password?</a>
                            @endif
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Sign In End -->

    <!-- JS Files -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
