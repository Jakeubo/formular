<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-900 dark:text-gray-100 leading-tight">
                👤 Můj profil
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Správa osobních údajů a zabezpečení účtu
            </p>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- 🧾 Údaje o profilu -->
            <div class="p-6 sm:p-8 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-md transition hover:shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Osobní údaje
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Aktualizuj své jméno, e-mail nebo jiné kontaktní údaje.
                </p>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- 🔒 Změna hesla -->
            <div class="p-6 sm:p-8 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-md transition hover:shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    🔑 Změna hesla
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Změň své heslo pro větší bezpečnost účtu.
                </p>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- 🗑️ Smazání účtu -->
            <div class="p-6 sm:p-8 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-md transition hover:shadow-lg">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    ⚠️ Zrušení účtu
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Trvale odstraní tvůj účet a všechna s ním spojená data. Tato akce je nevratná.
                </p>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
