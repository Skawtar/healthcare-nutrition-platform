<x-guest-layout>
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-blue-500 mb-2">Verify Your Email</h1>
                <p class="text-gray-600 mb-4">A 6-digit verification code has been sent to **{{ Auth::user()->email }}**.</p>
                <p class="text-gray-600">Please enter the code below to verify your account.</p>
            </div>

            <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

            {{-- Using x-input-error for specific field errors --}}
            {{-- If you have a general x-auth-validation-errors component, you can use that too --}}

            <form method="POST" action="{{ route('verification.verify.code') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="code" :value="__('Verification Code')" class="block text-sm font-medium text-blue-600" />
                    <x-text-input
                        id="code"
                        class="mt-1 block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                        type="text"
                        name="code"
                        required
                        autofocus
                        placeholder="e.g., 123456"
                    />
                    <x-input-error :messages="$errors->get('code')" class="mt-2 text-sm text-red-600" />
                </div>

                <div class="mt-6">
                    <x-primary-button class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        {{ __('Verify Email') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('verification.resend.code') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-sm text-sky-600 hover:text-sky-500 hover:underline font-medium">
                    {{ __('Resend Verification Code') }}
                </button>
            </form>
    
</x-guest-layout>