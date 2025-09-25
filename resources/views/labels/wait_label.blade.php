<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Štítek zásilky</title>
  <style>
    body { font-family: sans-serif; text-align: center; margin-top: 50px; }
    .loader { border: 6px solid #f3f3f3; border-radius: 50%; border-top: 6px solid #3498db;
              width: 60px; height: 60px; animation: spin 1s linear infinite; margin: 40px auto; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .hidden { display: none; }
    iframe { width: 90vw; height: 80vh; border: 1px solid #aaa; }
  </style>
</head>
<body>
  <h2>Váš štítek se připravuje...</h2>
  <div id="loader" class="loader"></div>
  <div id="pdf-container" class="hidden">
    <iframe id="pdf-frame"></iframe>
    <p><a id="pdf-download" href="#" download>📥 Stáhnout PDF</a></p>
  </div>

  <script>
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get('order');
    const carrier = params.get('carrier'); // např. pplhome, pplparcel, balikovna, zasilkovna
    const pollInterval = 3000;
    let batchId = null;

    async function fetchOrPoll() {
      if (!orderId || !carrier) {
        alert("Chybí parametry order/carrier!");
        return;
      }

      let url = `/labels/${carrier}/${orderId}`;
      if (batchId) url += `?batchId=${batchId}`;

      try {
        const res = await fetch(url);

        if (res.ok && res.headers.get('content-type').includes('application/pdf')) {
          const blob = await res.blob();
          const pdfUrl = URL.createObjectURL(blob);

          document.getElementById('loader').classList.add('hidden');
          document.getElementById('pdf-container').classList.remove('hidden');
          document.getElementById('pdf-frame').src = pdfUrl;
          document.getElementById('pdf-download').href = pdfUrl;
          return;
        }

        if (res.ok && res.headers.get('content-type').includes('application/json')) {
          const data = await res.json();
          if (data.batchId) {
            batchId = data.batchId;
          }
        }
      } catch (e) {
        console.error(e);
      }

      setTimeout(fetchOrPoll, pollInterval);
    }

    fetchOrPoll();
  </script>
</body>
</html>
