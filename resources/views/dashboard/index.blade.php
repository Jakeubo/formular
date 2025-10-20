<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-2xl text-gray-900">
                📊 Přehled příjmů – {{ $selectedYear }}
            </h2>

            <form method="GET" action="{{ route('dashboard.index') }}">

                <select name="year" onchange="this.form.submit()"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm hover:border-indigo-400 focus:ring-2 focus:ring-indigo-400">
                    @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($y==$selectedYear)>
                        {{ $y }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>



    <div class="py-10 bg-gray-50">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Graf příjmů -->
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                <canvas id="incomeChart" height="100"></canvas>
            </div>

            <!-- Souhrn + výběr měsíce -->
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 text-center space-y-3 relative">
                <div class="absolute top-4 right-4">
                    <select id="monthSelect"
                        class="bg-indigo-600 text-white font-medium rounded-lg px-4 py-2 text-sm shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="0" selected>Celý rok</option>
                        <option value="1">Leden</option>
                        <option value="2">Únor</option>
                        <option value="3">Březen</option>
                        <option value="4">Duben</option>
                        <option value="5">Květen</option>
                        <option value="6">Červen</option>
                        <option value="7">Červenec</option>
                        <option value="8">Srpen</option>
                        <option value="9">Září</option>
                        <option value="10">Říjen</option>
                        <option value="11">Listopad</option>
                        <option value="12">Prosinec</option>
                    </select>
                </div>

                <p class="text-lg font-semibold text-gray-800">
                    Celkové příjmy za rok {{ $selectedYear }}:
                    <span id="totalIncome" class="text-indigo-600">
                        {{ number_format(array_sum($months->toArray()), 2, ',', ' ') }} Kč
                    </span>
                </p>

                <p class="text-md font-medium text-gray-700">
                    Z toho tvoří doprava:
                    <span id="totalShipping" class="text-green-600 font-semibold">
                        {{ number_format($totalShipping, 2, ',', ' ') }} Kč
                    </span>
                    <!-- <span id="shippingPercent" class="text-gray-500">
                        <p class="text-md font-medium text-gray-700">
                            Z toho tvoří doprava:
                            <span id="totalShipping" class="text-green-600 font-semibold">
                                {{ number_format($totalShipping, 2, ',', ' ') }} Kč
                            </span>
                            <span id="shippingPercent" class="text-gray-500">
                                @php
                                $totalIncome = array_sum($months->toArray());
                                @endphp
                                ({{ $totalIncome > 0 ? number_format(($totalShipping / $totalIncome) * 100, 1, ',', ' ') : '0,0' }} %)
                            </span>
                        </p>

                    </span> -->
                </p>
            </div>

            <!-- Koláč dopravců -->
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">🚚 Využití dopravců</h3>
                <div class="flex justify-center">
                    <div class="w-64 h-64 sm:w-72 sm:h-72 relative">
                        <canvas id="carrierChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="incomeData"
        data-income='@json($incomeData)'
        data-shipping='@json($shippingData)'
        data-carriers='@json($carrierStats ?? [])'
        data-carriers-month='@json($carrierStatsByMonth ?? [])'></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

    <script>
        const el = document.getElementById('incomeData');
        const incomeData = JSON.parse(el.dataset.income);
        const shippingData = JSON.parse(el.dataset.shipping);
        const carrierStats = JSON.parse(el.dataset.carriers);
        const carrierStatsByMonth = JSON.parse(el.dataset.carriersMonth);

        const months = [
            'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
            'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'
        ];

        // 🎨 Barvy dopravců
        function carrierColor(label) {
            const name = label.toLowerCase();
            if (name.includes('zasil')) return '#ef4444';
            if (name.includes('balik')) return '#facc15';
            if (name.includes('ppl')) return '#3b82f6';
            if (name.includes('osob')) return '#22c55e';
            return '#a855f7';
        }

        // 📊 Graf příjmů
        const ctxIncome = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(ctxIncome, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'Celkové příjmy (Kč)',
                        data: incomeData,
                        backgroundColor: '#4f46e5',
                        borderRadius: 6
                    },
                    {
                        label: 'Příjmy dopravců (Kč)',
                        data: shippingData,
                        backgroundColor: '#22c55e',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v.toLocaleString('cs-CZ') + ' Kč'
                        }
                    }
                }
            }
        });

        // 🍩 Koláč dopravců
        const ctxCarrier = document.getElementById('carrierChart').getContext('2d');
        let carrierChart = new Chart(ctxCarrier, {
            type: 'doughnut',
            plugins: [ChartDataLabels], // ✅ aktivace pluginu
            data: {
                labels: Object.keys(carrierStats),
                datasets: [{
                    data: Object.values(carrierStats),
                    backgroundColor: Object.keys(carrierStats).map(l => carrierColor(l)),
                    hoverOffset: 8
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percent = ((ctx.raw / total) * 100).toFixed(1);
                                return `${ctx.label}: ${ctx.raw} (${percent} %)`; // tooltip
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 13
                        },
                        formatter: function(value, ctx) {
                            const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${percent}%`; // můžeš změnit na `${value}` pro absolutní počet
                        }
                    }
                }
            }
        });


        // 🧮 Souhrn přepínač
        const select = document.getElementById('monthSelect');
        const incomeText = document.getElementById('totalIncome');
        const shipText = document.getElementById('totalShipping');
        const percentText = document.getElementById('shippingPercent');

        select.addEventListener('change', e => {
            const month = parseInt(e.target.value);
            const totalIncome = month === 0 ? incomeData.reduce((a, b) => a + b, 0) : incomeData[month - 1];
            const totalShip = month === 0 ? shippingData.reduce((a, b) => a + b, 0) : shippingData[month - 1];
            const percent = totalIncome > 0 ? (totalShip / totalIncome) * 100 : 0;

            // Souhrn text
            incomeText.textContent = totalIncome.toLocaleString('cs-CZ', {
                minimumFractionDigits: 2
            }) + ' Kč';
            shipText.textContent = totalShip.toLocaleString('cs-CZ', {
                minimumFractionDigits: 2
            }) + ' Kč';
            percentText.textContent = `(${percent.toFixed(1)} %)`;

            // Aktualizace sloupcového grafu
            incomeChart.data.datasets[0].backgroundColor = months.map((_, i) =>
                month === 0 || i === (month - 1) ? '#4f46e5' : '#c7d2fe'
            );
            incomeChart.data.datasets[1].backgroundColor = months.map((_, i) =>
                month === 0 || i === (month - 1) ? '#22c55e' : '#bbf7d0'
            );
            incomeChart.update();

            // Aktualizace koláče podle dopravců v daném měsíci
            let data, labels;
            if (month === 0) {
                labels = Object.keys(carrierStats);
                data = Object.values(carrierStats);
            } else {
                const monthStats = carrierStatsByMonth[month] || {};
                labels = Object.keys(monthStats);
                data = Object.values(monthStats);
            }

            carrierChart.data.labels = labels;
            carrierChart.data.datasets[0].data = data;
            carrierChart.data.datasets[0].backgroundColor = labels.map(l => carrierColor(l));
            carrierChart.update();
        });
    </script>
</x-app-layout>