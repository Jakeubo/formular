<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-900">
            📊 Přehled příjmů – {{ $year }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Graf -->
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                <canvas id="incomeChart" height="100"></canvas>
            </div>

            <!-- Souhrn -->
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 text-center">
                <p class="text-lg font-semibold text-gray-800">
                    Celkové příjmy za rok {{ $year }}:
                    <span class="text-indigo-600">
                        {{ number_format(array_sum($months->toArray()), 2, ',', ' ') }} Kč
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- skryté data -->
    <div id="incomeData" data-values='@json($incomeData)'></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const raw = document.getElementById('incomeData').dataset.values;
        const incomeData = JSON.parse(raw);

        new Chart(document.getElementById('incomeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: [
                    'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
                    'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'
                ],
                datasets: [{
                    label: 'Příjmy (Kč)',
                    data: incomeData,
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }},
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('cs-CZ') + ' Kč';
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
