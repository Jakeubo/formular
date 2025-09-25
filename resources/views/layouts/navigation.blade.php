<nav x-data="{ open: false, profileOpen: false }"
    class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 
           shadow-lg backdrop-blur-md border-b border-white/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <!-- Logo -->
            <div class="flex items-center space-x-2 text-white font-bold text-xl tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-7 w-7 text-yellow-300"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                <span>Moje Fakturace</span>
            </div>

            <!-- Navigační odkazy -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                <a href="{{ route('dashboard.index') }}"
                   class="flex items-center px-3 py-2 text-white font-medium rounded-xl transition 
                   hover:bg-white/20 hover:shadow-md {{ request()->routeIs('dashboard.*') ? 'bg-white/30' : '' }}">
                    📊 <span class="ml-2">Přehled</span>
                </a>

                <a href="{{ route('invoices.index') }}"
                   class="flex items-center px-3 py-2 text-white font-medium rounded-xl transition 
                   hover:bg-white/20 hover:shadow-md {{ request()->routeIs('invoices.*') ? 'bg-white/30' : '' }}">
                    🧾 <span class="ml-2">Faktury</span>
                </a>

                <a href="{{ route('bank-payments.index') }}"
                   class="flex items-center px-3 py-2 text-white font-medium rounded-xl transition 
                   hover:bg-white/20 hover:shadow-md {{ request()->routeIs('bank-payments.*') ? 'bg-white/30' : '' }}">
                    💳 <span class="ml-2">Platby</span>
                </a>

                <a href="{{ route('customers.index') }}"
                   class="flex items-center px-3 py-2 text-white font-medium rounded-xl transition 
                   hover:bg-white/20 hover:shadow-md {{ request()->routeIs('customers.*') ? 'bg-white/30' : '' }}">
                    👥 <span class="ml-2">Zákazníci</span>
                </a>
            </div>

<!-- Profil + dropdown -->
<div class="relative flex items-center space-x-3" x-data="{ profileOpen: false }">
    <button @click="profileOpen = !profileOpen" 
            class="flex items-center space-x-2 focus:outline-none">
        <span class="text-white font-medium">
            Ahoj, {{ Auth::user()->name ?? 'Uživatel' }}
        </span>
        <img class="h-10 w-10 rounded-full border-2 border-white shadow cursor-pointer"
            src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=6d28d9&color=fff"
            alt="avatar">
    </button>

    <!-- Dropdown -->
    <div x-cloak
         x-show="profileOpen"
         @click.away="profileOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute right-0 mt-12 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
        
        <a href="{{ route('profile.edit') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            ⚙️ Profil & Nastavení
        </a>
        
        <a href="{{ route('password.request') }}"
           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
            🔑 Změnit heslo
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                🚪 Odhlásit se
            </button>
        </form>
    </div>
</div>

        </div>
    </div>
</nav>
