<section class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 space-y-6 border border-gray-100">
    <header>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">
            Údaje o profilu
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Uprav své jméno nebo e-mailovou adresu přidruženou k účtu.
        </p>
    </header>

    <!-- Odeslání ověřovacího e-mailu -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <!-- Jméno -->
        <div>
            <x-input-label for="name" value="Jméno" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400"
                :value="old('name', $user->name)"
                required autofocus autocomplete="name"
                placeholder="Zadej své jméno" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- E-mail -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400"
                :value="old('email', $user->email)"
                required autocomplete="username"
                placeholder="např. jan.novak@email.cz" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            <!-- Ověření e-mailu -->
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-sm text-gray-800 dark:text-gray-200">
                        Tvůj e-mail není ověřen.
                        <button form="send-verification"
                            class="underline text-indigo-600 hover:text-indigo-800 font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-md">
                            Klikni zde pro opětovné odeslání ověřovacího e-mailu.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            📧 Nový ověřovací odkaz byl odeslán na tvůj e-mail.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow transition">
                💾 Uložit změny
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-green-600 font-medium"
                >
                    ✅ Změny byly uloženy.
                </p>
            @endif
        </div>
    </form>
</section>
