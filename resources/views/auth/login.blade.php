<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" 
                   class="form-control @error('email') is-invalid @enderror" 
                   placeholder="Enter your email" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-white-50">Forgot?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="••••••••" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
            <label class="form-check-label text-white-50 small" for="remember_me">
                Keep me logged in
            </label>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-login">
                SIGN IN <i class="fas fa-sign-in-alt ms-2"></i>
            </button>
        </div>
    </form>
</x-guest-layout>
