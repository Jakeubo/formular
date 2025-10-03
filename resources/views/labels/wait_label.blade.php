<!DOCTYPE html>
<html lang="cs">

<head>
  <meta charset="UTF-8">
  <title>Štítek zásilky</title>
  <style>
    body {
      font-family: sans-serif;
      text-align: center;
      margin-top: 50px;
    }

    .loader {
      border: 6px solid #f3f3f3;
      border-radius: 50%;
      border-top: 6px solid #3498db;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
      margin: 40px auto;
    }

    @keyframes spin {
      100% {
        transform: rotate(360deg);
      }
    }

    .hidden {
      display: none;
    }

    iframe {
      width: 90vw;
      height: 80vh;
      border: 1px solid #aaa;
    }
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
  // token = poslední část cesty
  const pathParts = window.location.pathname.split('/');
  const orderId = pathParts[pathParts.length - 1];

  // carrier = query param
  const params = new URLSearchParams(window.location.search);
  const carrier = params.get('carrier');

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

        // === pokud přijde PDF ===
        if (res.ok && res.headers.get('content-type')?.includes('application/pdf')) {
          const blob = await res.blob();
          const pdfUrl = URL.createObjectURL(blob);

          document.getElementById('loader').classList.add('hidden');
          document.getElementById('pdf-container').classList.remove('hidden');
          document.getElementById('pdf-frame').src = pdfUrl;
          document.getElementById('pdf-download').href = pdfUrl;
          return; // konec pollingu
        }

        // === pokud přijde JSON ===
        if (res.ok && res.headers.get('content-type')?.includes('application/json')) {
          const data = await res.json();

          if (data.status === 'pending') {
            if (data.batchId) batchId = data.batchId;
            setTimeout(fetchOrPoll, pollInterval); // čekáme dál
          } else if (data.error) {
            document.getElementById('loader').classList.add('hidden');
            alert("Chyba při generování štítku: " + data.error);
          }
          return;
        }

        // === pokud přijde něco jiného ===
        console.warn("Neočekávaná odpověď:", await res.text());
        setTimeout(fetchOrPoll, pollInterval);

      } catch (e) {
        console.error("Chyba fetch:", e);
        setTimeout(fetchOrPoll, pollInterval);
      }
    }

    fetchOrPoll();
  </script>

</body>

</html>