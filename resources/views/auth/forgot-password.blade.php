<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | BPCM SySteM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Dashmin CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/fontawesome/css/all.min.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Forgot Password Start -->
    <div class="container-fluid">
        <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3 shadow">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo" style="width: 200px; height: 100px;">
                    </div>

                    <div class="text-center mb-3">
                        <h4 class="text-primary">Reset Password</h4>
                        <p class="text-muted small">
                            Forgot your password? No problem. Just enter your email address and we’ll send you a reset link.
                        </p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Forgot Password Form -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="form-floating mb-3">
                            <input id="email" type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus
                                   placeholder="Email address">
                            <label for="email">Email address</label>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary py-3 w-100 mb-3">
                            Email Password Reset Link
                        </button>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none small">Back to login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Forgot Password End -->

    <!-- JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
