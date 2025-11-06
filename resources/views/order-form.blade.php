<!DOCTYPE html>
<html lang="cs">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Objednávkový formulář Zapichnito3D – zápichy do dortu na přání.">
    <meta charset="UTF-8">
    <title>Formulář</title>
    <script src="https://widget.packeta.com/v6/www/js/library.js"></script>
    <script type="text/javascript" src="https://www.ppl.cz/sources/map/main.js" async></script>
    <link rel="stylesheet" href="https://www.ppl.cz/sources/map/main.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nerko+One&display=swap" rel="stylesheet">
</head>
</head>

<body class="bg-gradient-to-br from-[#E5B4D3] via-pink-200 to-[#BAEEE8] 
             bg-no-repeat bg-cover bg-center 
             min-h-screen p-2 sm:p-6 flex justify-center items-start">

    <form id="main_form" method="POST" action="{{ route('order.store') }}"
        class="w-full sm:max-w-xl md:max-w-xl 
                 bg-white/80 backdrop-blur-md rounded-2xl shadow-xl 
                 p-4 sm:p-6 space-y-5 font-sans">

        @csrf
        <input type="text" name="website" class="hidden">

        <h1 class="text-4xl sm:text-4xl text-center mb-6"
            style="color:#E5B4D3; font-family:'Nerko One', cursive;">
            Formulář Zapichnito3d
        </h1>

        <!-- Success message -->
        @if(session('success'))
        <div class="p-3 sm:p-4 mb-4 text-sm sm:text-base rounded-xl bg-green-100 text-green-800 border border-green-300 shadow">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="p-3 sm:p-4 mb-4 text-sm sm:text-base rounded-xl bg-red-100 text-red-800 border border-red-300 shadow">
            ❌ {{ session('error') }}
        </div>
        @endif

        <!-- Jméno -->
        <div>
            <label for="first_name" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Jméno</label>
            <input type="text" name="first_name" id="first_name" placeholder="Jméno" required
                class="w-full px-3 py-2 sm:px-4 sm:py-3 
                          border border-[#E5B4D3] rounded-xl shadow-sm 
                          focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition 
                          text-sm sm:text-base">
        </div>

        <!-- Příjmení -->
        <div>
            <label for="last_name" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Příjmení</label>
            <input type="text" name="last_name" id="last_name" placeholder="Příjmení" required
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
                          focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" id="email" placeholder="Email" required
                title="Zadejte platný e-mail (např. jmeno@domena.cz)"
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
               focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
        </div>

        <!-- Telefon -->
        <div>
            <label for="phone" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Telefon</label>
            <input type="tel" name="phone" id="phone" placeholder="Telefon" required
                pattern="^(\+420)?[0-9]{9}$"
                title="Zadejte telefonní číslo ve tvaru +420123456789 nebo 123456789"
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
               focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
        </div>


        <!-- Adresa -->
        <div>
            <label for="address" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Adresa</label>
            <input type="text" name="address" id="address" placeholder="Adresa" required
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
                          focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
        </div>

        <!-- Město + PSČ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="city" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Město</label>
                <input type="text" name="city" id="city" placeholder="Město" required
                    class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
                              focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
            </div>
            <div>
                <label for="zip" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">PSČ</label>
                <input type="text" name="zip" id="zip" placeholder="PSČ" required
                    pattern="^\d{3}\s?\d{2}$"
                    title="Zadejte PSČ ve formátu 12345 nebo 123 45"
                    class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
               focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
            </div>
        </div>

        <!-- Stát -->
        <div>
            <label for="country" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Stát</label>
            <select name="country" id="country" required
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl shadow-sm 
                           focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition text-sm sm:text-base">
                <option value="CZ">Česká republika</option>
                <option value="SK">Slovensko</option>
            </select>
        </div>

        <input type="hidden" name="carrier_id" id="carrier_id">
        <input type="hidden" name="carrier_address" id="carrier_address">

        <!-- Dopravce (listbox fancy) -->
        <div>
            <label for="listboxDopravce" class="block text-sm sm:text-base font-medium text-gray-700 mb-1">Dopravce</label>
            <select name="carrier" id="listboxDopravce" required
                class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-[#E5B4D3] rounded-xl">
                <option value="">--Vyberte dopravce--</option>
                @foreach($shippingMethods as $method)
                <option value="{{ $method->code }}">
                    {{ $method->name }} – {{ number_format($method->price, 0, ',', ' ') }} Kč
                </option>
                @endforeach
            </select>

        </div>

        <!-- Výběr výdejního místa -->
        <div class="space-y-2">
            <p id="selectedPoint" class="hidden px-3 py-2 text-sm sm:text-base rounded-lg bg-indigo-100 text-indigo-800 font-medium"></p>
            <p id="balikovnaSelected" class="hidden px-3 py-2 text-sm sm:text-base rounded-lg bg-pink-100 text-pink-700 font-medium"></p>
            <p id="pplSelected" class="hidden px-3 py-2 text-sm sm:text-base rounded-lg bg-blue-100 text-blue-700 font-medium"></p>
            <p id="osobniSelected" class="hidden px-3 py-2 text-sm sm:text-base rounded-lg bg-yellow-100 text-yellow-700 font-medium">
                📍 Osobní odběr: SushiHub, Sokolská 123, Olomouc
            </p>
        </div>

        <!-- GDPR souhlas -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-gray-700">
            <label class="flex items-start space-x-2">
                <input type="checkbox" name="gdpr" id="gdpr" required
                    class="mt-1 w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-400">
                <span>
                    Souhlasím se zpracováním osobních údajů za účelem vyřízení objednávky.
                    Více informací naleznete v
                    <a href="https://e.zapichnito3d.cz/content/7-gdpr-ochrana-osobnich-udaju" target="_blank" class="text-pink-600 underline hover:text-pink-800">
                        zásadách ochrany osobních údajů
                    </a>.
                </span>
            </label>
        </div>


        <!-- Fancy Button -->
        <button type="submit" id="submitBtn"
            class="w-full py-3 px-4 rounded-xl shadow font-semibold text-white 
                       bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400
                       hover:from-pink-500 hover:via-purple-500 hover:to-indigo-500
                       transform hover:scale-[1.03] transition duration-300 ease-in-out text-sm sm:text-base">
            ✨ Odeslat objednávku ✨
        </button>
    </form>

    <!-- Modal pro PPL výdejnu -->

    <div id="pplModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-2xl shadow-2xl 
                w-[95%] max-w-4xl h-[90vh] sm:h-[90%] p-4 sm:p-6 box-border overflow-hidden">

            <!-- Zavírací tlačítko -->
            <button id="closePpl"
                class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-2xl font-bold">
                ✕
            </button>

            <h3 class="text-base sm:text-xl font-semibold mb-2 sm:mb-4 text-center">
                Vyberte výdejní místo PPL
            </h3>

            <!-- Kontejner pro mapu -->
            <div id="ppl-parcelshop-map"
                class="w-full h-[calc(100%-3rem)] sm:h-[calc(100%-4rem)] border rounded-lg overflow-hidden"></div>
        </div>
    </div>



    <!-- Modal pro Balíkovnu -->
    <div id="balikovnaModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
        <div id="balikovnaBox" class="bg-white rounded-2xl shadow-2xl w-[95%] h-[90%] p-6 relative">
            <button id="closeBalikovna" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">✕</button>
            <h2 class="text-xl font-bold mb-4 text-center">Vyberte výdejní místo Balíkovna</h2>
            <iframe id="balikovnaIframe" src="" class="w-full h-[calc(100%-3rem)] border rounded-lg"></iframe>
        </div>
    </div>

    <!-- Blok pro osobní odběr -->
    <!-- Modal pro Osobní odběr -->
    <div id="osobniModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full h-full md:w-[80%] md:h-[80%] p-4 md:p-6 relative">
            <button id="closeOsobni" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-2xl font-bold">✕</button>
            <h2 class="text-lg md:text-xl font-bold mb-4 text-center">Osobní odběr</h2>
            <p class="text-center mb-2"><strong>SushiHub, Sokolská 123, 779 00 Olomouc</strong></p>
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1293.0388591510705!2d17.247702239001374!3d49.59628383138692!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47124f87da343ccb%3A0x221f9740582a45bd!2sSushiHub!5e0!3m2!1scs!2scz!4v1724142892372!5m2!1scs!2scz"
                class="w-full h-[75vh] md:h-[calc(100%-5rem)] rounded-lg border-0"
                allowfullscreen loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("main_form");
            const submitBtn = document.getElementById("submitBtn");

            if (form && submitBtn) {
                form.addEventListener("submit", function() {
                    submitBtn.disabled = true;
                    submitBtn.innerText = "Odesílám...";
                });
            }
        });
    </script>

    <script src="{{ asset('js/packeta.js') }}"></script>
    <script src="{{ asset('js/balikovna.js') }}"></script>
    <script src="{{ asset('js/osobni.js') }}"></script>
    <script src="{{ asset('js/resetui.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/ppl.js') }}"></script>

</body>

</html>