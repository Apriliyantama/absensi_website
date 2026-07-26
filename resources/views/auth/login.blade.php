<x-guest-layout>

    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">
            Login
        </h2>
        <p class="text-sm text-gray-500 mt-2">
            Silakan masuk ke sistem
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />

            <div class="relative mt-1">
                <i class="fa-solid fa-envelope absolute left-3 top-3 text-gray-400"></i>
                <x-text-input id="email" class="block w-full pl-10 rounded-lg" type="email" name="email"
                    :value="old('email')" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <i class="fa-solid fa-lock absolute left-3 top-3 text-gray-400"></i>
                <x-text-input id="password" class="block w-full pl-10 rounded-lg" type="password" name="password"
                    required autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <x-primary-button class="ms-3 px-5 py-2 bg-blue-600 hover:bg-blue-700">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

</x-guest-layout>
