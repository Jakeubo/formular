<nav x-data="{ open: false }" 
     class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 
            shadow-lg backdrop-blur-md border-b border-white/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- Logo / Název aplikace -->
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
            </div>

            <!-- User Menu / Profil -->
            <div class="flex items-center space-x-3">
                <span class="text-white font-medium">Ahoj, {{ Auth::user()->name ?? 'Uživatel' }}</span>
                <img class="h-10 w-10 rounded-full border-2 border-white shadow" 
                     src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=6d28d9&color=fff" 
                     alt="avatar">
            </div>
        </div>
    </div>
</nav>
