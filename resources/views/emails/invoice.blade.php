<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Faktura č. {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; margin:0; padding:0;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:auto;">
        <!-- Hlavička -->
        <tr>
            <td style="background:#E5B4D3; padding:20px; text-align:center; color:#fff; font-size:22px; font-weight:bold;">
                ZapichniTo3D
            </td>
        </tr>

        <!-- Obsah -->
        <tr>
            <td style="background:#fff; padding:30px; border:1px solid #eee;">
                <p style="font-size:16px; margin-bottom:20px;">Dobrý den,</p>

                <p style="font-size:16px; margin-bottom:20px;">
                    byla pro vás vystavena <strong>faktura č. {{ $invoice->invoice_number }}</strong>.
                </p>

                <div style="background:#f9fafb; border:1px solid #ddd; border-radius:8px; padding:15px; margin-bottom:20px; text-align:center;">
                    <p style="font-size:18px; margin:0;">Celková částka k úhradě:</p>
                    <p style="font-size:24px; color:#4f46e5; margin:10px 0 0;">
                        <strong>{{ number_format($invoice->total_price, 2, ',', ' ') }} Kč</strong>
                    </p>
                </div>

                <p style="font-size:16px; margin-bottom:30px;">
                    Fakturu si můžete stáhnout kliknutím na tlačítko níže:
                </p>

                <p style="text-align:center;">
                    <a href="{{ route('invoices.download', $invoice) }}" 
                       style="background:#4f46e5; color:#fff; text-decoration:none; padding:12px 24px; 
                              border-radius:8px; font-weight:bold; font-size:16px;">
                        📎 Stáhnout fakturu
                    </a>
                </p>

                <p style="margin-top:30px; font-size:14px; color:#555;">
                    Děkujeme za Vaši objednávku! <br>
                    <strong>ZapichniTo3D</strong><br>
                    <a href="https://zapichnito3d.cz" style="color:#4f46e5;">www.zapichnito3d.cz</a>
                </p>
            </td>
        </tr>

        <!-- Patička -->
        <tr>
            <td style="background:#BAEEE8; padding:15px; text-align:center; font-size:12px; color:#333;">
                Tento e-mail byl vygenerován automaticky, neodpovídejte na něj.
            </td>
        </tr>
    </table>
</body>
</html>
