<!DOCTYPE html>
<html lang="cs">

<head>
  <meta charset="UTF-8">
  <title>Štítek zásilky</title>
  <style>
    body {
      font-family: system-ui, sans-serif;
      text-align: center;
      padding: 50px 20px;
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

    .success {
      color: #2ecc71;
    }

    #timer {
      margin-top: 15px;
      color: #777;
      font-size: 0.95rem;
    }
  </style>
</head>

<body>
  <h2 id="status-text">Váš štítek se připravuje...</h2>
  <div id="loader" class="loader"></div>
  <div id="timer">Čekám 0 s...</div>

  <div id="pdf-container" class="hidden">
    <p class="success">✅ Štítek je připraven!</p>
    <iframe id="pdf-frame"></iframe>
    <p><a id="pdf-download" href="#" download>📥 Stáhnout PDF</a></p>
  </div>

  <script>
    let batchId = null;
    let elapsedSeconds = 0;
    let attempt = 0;

    // token + carrier
    const pathParts = window.location.pathname.split('/');
    const orderToken = pathParts[pathParts.length - 1];
    const params = new URLSearchParams(window.location.search);
    const carrier = params.get('carrier');

    // aktualizace časovače
    setInterval(() => {
      elapsedSeconds++;
      document.getElementById('timer').textContent = `⏳ Čekám ${elapsedSeconds} s...`;
    }, 1000);

    async function fetchOrPoll() {
      attempt++;
      const pollInterval = attempt < 30 ? 1000 : 3000; // první 30s = 1 s, pak 3 s

      if (!orderToken || !carrier) {
        alert("❌ Chybí parametry objednávky nebo dopravce.");
        return;
      }

      let url = `/label/${carrier}/${orderToken}`;
      if (batchId) url += `?batchId=${batchId}`;

      try {
        const res = await fetch(url, { cache: 'no-store' });

        // === PDF připravené ===
        if (res.ok && res.headers.get('content-type')?.includes('application/pdf')) {
          const blob = await res.blob();
          const pdfUrl = URL.createObjectURL(blob);

          document.getElementById('loader').classList.add('hidden');
          document.getElementById('timer').classList.add('hidden');
          document.getElementById('status-text').textContent = '✅ Štítek připraven';
          document.getElementById('pdf-container').classList.remove('hidden');
          document.getElementById('pdf-frame').src = pdfUrl;
          document.getElementById('pdf-download').href = pdfUrl;

          // automaticky otevřít PDF v nové záložce
          window.open(pdfUrl, '_blank');
          return;
        }

        // === JSON odpověď ===
        if (res.ok && res.headers.get('content-type')?.includes('application/json')) {
          const data = await res.json();

          if (data.status === 'pending') {
            if (data.batchId) batchId = data.batchId;
            console.log(`⏳ čekám... ${elapsedSeconds}s (batchId=${batchId})`);
            setTimeout(fetchOrPoll, pollInterval);
          } else if (data.error) {
            document.getElementById('loader').classList.add('hidden');
            document.getElementById('timer').classList.add('hidden');
            document.getElementById('status-text').textContent = "⚠️ Chyba: " + data.error;
          } else {
            console.warn("Neznámá odpověď:", data);
            setTimeout(fetchOrPoll, pollInterval);
          }
          return;
        }

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
