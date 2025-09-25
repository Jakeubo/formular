<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            PPL štítek – objednávka #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="p-6">
        <div id="loader" class="text-center">
            <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p>Generuji štítek, čekejte prosím...</p>
        </div>

        <div id="pdf-container" class="hidden">
            <iframe id="pdf-frame" class="w-full h-[80vh] border"></iframe>
            <div class="mt-4 text-center">
                <a id="pdf-download" href="#" download class="bg-indigo-600 text-white px-4 py-2 rounded">
                    Stáhnout PDF
                </a>
            </div>
        </div>
    </div>

    <script>
        let batchId = null;
        const pollInterval = 3000;

        async function fetchOrPoll() {
            let url = "{{ route('labels.ppl', $order) }}";
            if (batchId) url += "?batchId=" + encodeURIComponent(batchId);

            try {
                const res = await fetch(url);

                // PDF
                if (res.ok && res.headers.get('content-type').includes('application/pdf')) {
                    const blob = await res.blob();
                    const pdfUrl = URL.createObjectURL(blob);

                    document.getElementById('loader').classList.add('hidden');
                    document.getElementById('pdf-container').classList.remove('hidden');
                    document.getElementById('pdf-frame').src = pdfUrl;
                    document.getElementById('pdf-download').href = pdfUrl;
                    return;
                }

                // JSON
                if (res.ok && res.headers.get('content-type').includes('application/json')) {
                    const data = await res.json();
                    if (data.batchId) batchId = data.batchId;
                }
            } catch (err) {
                console.error("Chyba:", err);
            }

            setTimeout(fetchOrPoll, pollInterval);
        }

        fetchOrPoll();
    </script>
</x-app-layout>
