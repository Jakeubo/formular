<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#E5B4D3] via-purple-300 to-[#BAEEE8] p-6">
        <div class="w-full max-w-md bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-8">
            
            <!-- Logo / Nadpis -->
            <div class="text-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="mx-auto h-12 w-12 text-purple-600" 
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4v16m8-8H4" />
                </svg>
                <h2 class="mt-4 text-2xl font-bold text-gray-800">
                    Přihlášení do administrace
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Vítej zpět 👋
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
                    <x-text-input id="email" type="email" name="email" 
                        class="block px-5 mt-1 w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200"
                        :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Heslo')" class="text-gray-700 font-medium" />
                    <x-text-input id="password" type="password" name="password" 
                        class="block px-5 mt-1 w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200"
                        required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" />
                    <label for="remember_me" class="ml-2 text-sm text-gray-600">Zapamatovat</label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-purple-600 hover:text-purple-800" 
                           href="{{ route('password.request') }}">
                            Zapomněl jsi heslo?
                        </a>
                    @endif

                    <x-primary-button class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-xl shadow-md">
                        Přihlásit se
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
