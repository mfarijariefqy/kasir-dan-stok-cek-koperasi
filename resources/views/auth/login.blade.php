<x-guest-layout>
    @if(session('status'))
        <div class="session-status">
            <i class="fas fa-check-circle mr-1"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email / Username -->
        <div class="form-group">
            <label for="email">Email / Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user input-icon"></i>
                <input id="email" type="text" name="email"
                    value="{{ old('email') }}"
                    placeholder="Email atau username pelanggan"
                    required autofocus autocomplete="username">
            </div>
            @error('email')
                <div class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input id="password" type="password" name="password"
                    placeholder="••••••••"
                    required autocomplete="current-password">
            </div>
            @error('password')
                <div class="error-msg"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="remember-row">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">Ingat saya</label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
        </button>

        @if(Route::has('password.request'))
            <a class="forgot-link" href="{{ route('password.request') }}">
                <i class="fas fa-key mr-1"></i>Lupa password?
            </a>
        @endif
    </form>
</x-guest-layout>
