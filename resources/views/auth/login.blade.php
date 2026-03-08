<x-guest-layout>
      <div class="text-center">
                <h1 class="text-3xl font-extrabold text-blue-500 mb-2">Welcome Back!</h1>
                <p class="text-gray-600">Sign in to your account</p>
            </div>

            <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label class="block text-sm font-medium text-blue-600" for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        class="mt-1 block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <div class="mt-4">
                    <x-input-label class="block text-sm font-medium text-blue-600" for="password" :value="__('Password')" />
                    <x-text-input
                        id="password"
                        class="mt-1 block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                    </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        <span class="ml-2 text-sm text-gray-900">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-sky-600 hover:text-sky-500 hover:underline font-medium" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <div class="mt-6">
                    <x-primary-button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>
       
    
</x-guest-layout>