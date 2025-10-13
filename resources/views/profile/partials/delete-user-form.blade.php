<section class="space-y-6 bg-white dark:bg-gray-800 shadow rounded-xl p-6 border border-gray-100">
    <header>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">
            ⚠️ Smazání účtu
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Po smazání účtu budou <strong>všechna data trvale odstraněna</strong>.  
            Před potvrzením si prosím stáhni nebo ulož všechny informace, které chceš zachovat.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md transition"
    >
        🗑️ Smazat účet
    </x-danger-button>

    <!-- Potvrzovací modál -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Opravdu chceš smazat svůj účet?
            </h2>

            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                Tato akce <strong>je nevratná</strong>.  
                Po potvrzení budou všechna tvoje data, objednávky a osobní údaje smazány.
                Pro potvrzení zadej své heslo.
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="Heslo" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 rounded-lg border-gray-300 focus:border-red-500 focus:ring-2 focus:ring-red-400"
                    placeholder="Zadej své heslo"
                    required
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button
                    x-on:click="$dispatch('close')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                >
                    Zrušit
                </x-secondary-button>

                <x-danger-button
                    class="ms-3 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md transition"
                >
                    🗑️ Trvale smazat účet
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
