<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LabelController extends Controller
{

    public function pplParcelshop(string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();

        $clientId     = config('services.ppl.client_id');
        $clientSecret = config('services.ppl.client_secret');
        $tokenUrl     = 'https://api.dhl.com/ecs/ppl/myapi2/login/getAccessToken';

        // 1️⃣ Access token
        $tokenResp = Http::asForm()->post($tokenUrl, [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($tokenResp->failed()) {
            Log::error('❌ PPL ParcelShop token error', ['body' => $tokenResp->body()]);
            return response()->json(['error' => '❌ Nepodařilo se získat access token PPL'], 500);
        }

        $accessToken = $tokenResp->json('access_token');

        // 2️⃣ Pokud přišel batchId → pokus o PDF
        if (request()->has('batchId')) {
            $batchId = request('batchId');
            $labelUrl = "https://api.dhl.com/ecs/ppl/myapi2/shipment/batch/$batchId/label?limit=1&offset=0&PageSize=A4";

            $pdfResp = Http::withHeaders([
                "Authorization" => "Bearer $accessToken",
                "Accept"        => "application/pdf",
            ])->get($labelUrl);

            if ($pdfResp->ok() && str_contains($pdfResp->header('Content-Type'), 'application/pdf')) {
                $pdf = $pdfResp->body();

                if (!$order->tracking_number) {
                    $order->tracking_number = $batchId;
                    $order->save();
                }

                Log::info('✅ PPL ParcelShop PDF připraven', ['batchId' => $batchId]);
                return response($pdf, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="ppl-parcelshop-label.pdf"');
            }

            Log::warning('⏳ PPL ParcelShop PDF zatím není připraveno', ['batchId' => $batchId]);
            return response()->json(['status' => 'pending', 'batchId' => $batchId]);
        }

        // 3️⃣ Payload pro vytvoření zásilky ParcelShop
        $payload = [
            "shipments" => [[
                "productType" => "SMAR", // Smart – ParcelShop
                "referenceId" => (string) $order->id,
                "note"        => "Zásilka přes ZapichniTo3D.cz",
                "depot"       => "01",
                "shipmentSet" => ["numberOfShipments" => 1],
                "sender" => [
                    "name"    => "ZapichniTo3D",
                    "street"  => "Žižkova 1031",
                    "city"    => "Velká Bystřice",
                    "zipCode" => "78353",
                    "country" => "CZ",
                    "phone"   => "123456789",
                    "email"   => "info@zapichnito3d.cz",
                ],
                "recipient" => [
                    "name"    => "{$order->first_name} {$order->last_name}",
                    "street"  => mb_substr($order->carrier_address ?? 'ParcelShop', 0, 60),
                    "city"    => $order->city,
                    "zipCode" => $order->zip,
                    "country" => $order->country ?? "CZ",
                    "phone"   => $order->phone,
                    "email"   => $order->email,
                ],
                "specificDelivery" => [
                    "parcelShopCode" => $order->carrier_id, // např. CZ123456
                ],
            ]],
            "labelSettings" => [
                "format" => "Pdf",
                "dpi"    => 300,
                "completeLabelSettings" => [
                    "isCompleteLabelRequested" => true,
                ],
            ],
        ];

        $batchResp = Http::withToken($accessToken)
            ->withHeaders(["Content-Type" => "application/json"])
            ->post('https://api.dhl.com/ecs/ppl/myapi2/shipment/batch', $payload);

        if ($batchResp->failed()) {
            Log::error('❌ PPL ParcelShop create shipment error', [
                'status' => $batchResp->status(),
                'body'   => $batchResp->body(),
            ]);
            return response()->json(['error' => '❌ PPL API error', 'body' => $batchResp->body()], 500);
        }

        // 4️⃣ Získání batchId
        $location = $batchResp->header('Location');
        $batchId  = $location ? basename($location) : null;

        if (!$batchId) {
            Log::error('❌ PPL ParcelShop nevrátil batchId', ['response' => $batchResp->body()]);
            return response()->json(['error' => '❌ PPL nevrátil batchId.'], 500);
        }

        Log::info('✅ PPL ParcelShop shipment vytvořen', ['order' => $order->id, 'batchId' => $batchId]);

        // 5️⃣ Nastavení formátu štítku (PUT)
        $putUrl = "https://api.dhl.com/ecs/ppl/myapi2/shipment/batch/$batchId";
        $putPayload = [
            "labelSettings" => [
                "format" => "Pdf",
                "completeLabelSettings" => [
                    "isCompleteLabelRequested" => true,
                    "pageSize" => "A4",
                ],
            ],
        ];

        $putResp = Http::withToken($accessToken)
            ->withHeaders(["Content-Type" => "application/json"])
            ->put($putUrl, $putPayload);

        if ($putResp->failed()) {
            Log::warning('⚠️ PPL ParcelShop PUT update error', [
                'status' => $putResp->status(),
                'body'   => $putResp->body(),
            ]);
        }

        // ✅ Vrať batchId → frontend začne polling
        return response()->json([
            'status'  => 'pending',
            'batchId' => $batchId,
        ]);
    }




    /**
     * Vytvoření a tisk štítku PPL (DHL API)
     */
    public function ppl(string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();

        $clientId     = config('services.ppl.client_id');
        $clientSecret = config('services.ppl.client_secret');
        $tokenUrl     = 'https://api.dhl.com/ecs/ppl/myapi2/login/getAccessToken';

        // 1️⃣ Získání access tokenu
        $tokenResp = Http::asForm()->post($tokenUrl, [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($tokenResp->failed()) {
            Log::error('❌ PPL token error', [
                'status' => $tokenResp->status(),
                'body'   => $tokenResp->body(),
            ]);
            return response()->json(['error' => '❌ Nepodařilo se získat access token.'], 500);
        }

        $accessToken = $tokenResp->json('access_token');
        Log::info('✅ PPL access token získán', ['token' => $accessToken]);

        // 2️⃣ Pokud už máme batchId → stáhnout PDF štítek
        if (request()->has('batchId')) {
            $batchId = request('batchId');
            $labelUrl = "https://api.dhl.com/ecs/ppl/myapi2/shipment/batch/$batchId/label?limit=1&offset=0&PageSize=A4";

            $pdfResp = Http::withHeaders([
                "Authorization" => "Bearer $accessToken",
                "Accept"        => "application/pdf",
            ])->get($labelUrl);

            if ($pdfResp->ok() && str_contains($pdfResp->header('Content-Type'), 'application/pdf')) {
                $pdf = $pdfResp->body();

                if (!$order->tracking_number) {
                    $order->tracking_number = $batchId;
                    $order->save();
                }

                Log::info('✅ PPL štítek PDF připraven', ['order' => $order->id, 'batchId' => $batchId]);
                return response($pdf, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="ppl-label.pdf"');
            }

            Log::warning('⏳ PPL PDF ještě není připraveno', ['batchId' => $batchId]);
            return response()->json(['status' => 'pending', 'batchId' => $batchId]);
        }

        // 3️⃣ Vytvoření zásilky (pokud batchId ještě není)
        $payload = [
            "shipments" => [[
                "referenceId" => (string) $order->id,
                "productType" => "BUSS", // PPL Home
                "note"        => "Zásilka ZapichniTo3D.cz",
                "depot"       => "01",
                "shipmentSet" => ["numberOfShipments" => 1],
                "sender" => [
                    "name"    => "ZapichniTo3D",
                    "street"  => "Žižkova 1031",
                    "city"    => "Velká Bystřice",
                    "zipCode" => "78353",
                    "country" => "CZ",
                    "phone"   => "123456789",
                    "email"   => "info@zapichnito3d.cz",
                ],
                "recipient" => [
                    "name"    => "{$order->first_name} {$order->last_name}",
                    "street"  => $order->address,
                    "city"    => $order->city,
                    "zipCode" => $order->zip,
                    "country" => $order->country ?? "CZ",
                    "phone"   => $order->phone,
                    "email"   => $order->email,
                ],
            ]],
            "labelSettings" => [
                "format" => "Pdf",
                "dpi"    => 300,
                "completeLabelSettings" => [
                    "isCompleteLabelRequested" => true,
                ],
            ],
        ];

        $batchResp = Http::withToken($accessToken)
            ->withHeaders(["Content-Type" => "application/json"])
            ->post('https://api.dhl.com/ecs/ppl/myapi2/shipment/batch', $payload);

        if ($batchResp->failed()) {
            Log::error('❌ PPL create shipment error', [
                'status' => $batchResp->status(),
                'body'   => $batchResp->body(),
            ]);
            return response()->json(['error' => '❌ Chyba při vytvoření zásilky.', 'body' => $batchResp->body()], 500);
        }

        $location = $batchResp->header('Location');
        $batchId  = $location ? basename($location) : null;

        if (!$batchId) {
            Log::error('❌ PPL nevrátil batchId', ['response' => $batchResp->body()]);
            return response()->json(['error' => '❌ PPL nevrátil batchId.'], 500);
        }

        // 4️⃣ Aktualizace štítku (PUT)
        $putUrl = "https://api.dhl.com/ecs/ppl/myapi2/shipment/batch/$batchId";
        $putPayload = [
            "labelSettings" => [
                "format" => "Pdf",
                "completeLabelSettings" => [
                    "isCompleteLabelRequested" => true,
                    "pageSize" => "A4"
                ]
            ]
        ];

        $putResp = Http::withToken($accessToken)
            ->withHeaders(["Content-Type" => "application/json"])
            ->put($putUrl, $putPayload);

        if ($putResp->failed()) {
            Log::error('⚠️ PPL PUT batch update error', [
                'status' => $putResp->status(),
                'body'   => $putResp->body(),
            ]);
        }

        Log::info('✅ PPL shipment vytvořen', ['order' => $order->id, 'batchId' => $batchId]);

        // Počkej pár sekund, než API zpracuje štítek
        sleep(5);

        // 5️⃣ Pokus o stažení štítku
        $labelUrl = "https://api.dhl.com/ecs/ppl/myapi2/shipment/batch/$batchId/label?limit=1&offset=0&PageSize=A4";
        $pdfResp = Http::withHeaders([
            "Authorization" => "Bearer $accessToken",
            "Accept"        => "application/pdf",
        ])->get($labelUrl);

        if ($pdfResp->ok() && str_contains($pdfResp->header('Content-Type'), 'application/pdf')) {
            $pdf = $pdfResp->body();

            if (!$order->tracking_number) {
                $order->tracking_number = $batchId;
                $order->save();
            }

            Log::info('✅ PPL štítek PDF stažen ihned po vytvoření', ['batchId' => $batchId]);
            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="ppl-label.pdf"');
        }

        // Pokud ještě není hotový, vrať pending
        return response()->json([
            'status'  => 'pending',
            'batchId' => $batchId,
        ]);
    }




    public function zasilkovna(string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();
        $apiKey = config('services.zasilkovna.api_key');
        $wsdl = config('services.zasilkovna.wsdl');
        $wsdlBugfix = config('services.zasilkovna.wsdl_bugfix');

        try {
            // 1. Vytvoření zásilky
            $gw = new \SoapClient($wsdl);
            $packet = $gw->createPacket($apiKey, [
                'number'    => $order->id,
                'name'      => $order->first_name,
                'surname'   => $order->last_name,
                'email'     => $order->email,
                'phone'     => $order->phone,
                'addressId' => $order->carrier_id,  // id výdejního místa
                'value'     => 500,                 // hodnota zásilky
                'eshop'     => "Zapichnito3D.cz",
                'weight'    => 1,                   // váha v kg
            ]);

            $packetArray = get_object_vars($packet);
            $barcode = $packetArray['barcode'] ?? null;

            if (!$barcode) {
                return back()->with('error', '❌ Zásilka se nepodařila vytvořit.');
            }

            // 2. Stáhnout PDF štítek
            $client = new \SoapClient($wsdlBugfix);
            $pdfLabel = $client->packetLabelPdf($apiKey, $barcode, "A6 on A6", 0);

            // 3. Uložit tracking do DB
            if (!$order->tracking_number) {
                $order->tracking_number = $barcode;
                $order->save();
            }

            // 4. Vrátit PDF
            return response($pdfLabel, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="zasilkovna-label.pdf"');
        } catch (\SoapFault $e) {
            return back()->with('error', "❌ Chyba API Zásilkovny: " . $e->getMessage());
        }
    }

    /**
     * Vytvoření a tisk štítku Balíkovna (Česká pošta)
     */
    public function balikovna(string $token)
    {
        $order = Order::where('public_token', $token)->firstOrFail();
        $apiToken       = config('services.balikovna.api_token');
        $secretKey      = config('services.balikovna.secret_key');
        $customerId     = config('services.balikovna.customer_id');
        $contractNumber = config('services.balikovna.contract_number');

        $urlService  = "https://b2b.postaonline.cz:444/restservices/ZSKService/v1/parcelService";
        $urlPrinting = "https://b2b.postaonline.cz:444/restservices/ZSKService/v1/parcelPrinting";

        $timestamp = time();
        $nonce     = $this->generateUUIDv4();

        // 🟢 carrier_id z objednávky, např. "B78419" → "78419"
        $carrierId = substr($order->carrier_id, 1);

        // 1. Vytvoření zásilky
        $body = [
            "parcelServiceHeader" => [
                "parcelServiceHeaderCom" => [
                    "transmissionDate" => date("Y-m-d"),
                    "customerID"       => $customerId,
                    "postCode" => $order->zip,
                    "locationNumber"   => 2
                ],
                "printParams" => [
                    "idForm"          => 101,
                    "shiftHorizontal" => 0,
                    "shiftVertical"   => 0
                ],
                "position" => 1
            ],
            "parcelServiceData" => [
                "parcelParams" => [
                    "recordID"         => "1",
                    "prefixParcelCode" => "NB",
                    "weight"           => "1",
                    "insuredValue"     => 500,
                    "amount"           => 0,
                    "currency"         => "CZK",
                    // 🔑 vsParcel musí být číslo (1–10 číslic)
                    "vsParcel"         => (string) ($order->id % 1000000000),
                ],
                "parcelAddress" => [
                    "recordID"     => "12",
                    "firstName"    => $order->first_name,
                    "surname"      => $order->last_name,
                    "address" => [
                        "city"       => $order->city,
                        "zipCode"    => $carrierId,
                        "isoCountry" => $order->country ?? "CZ"
                    ],
                    "mobilNumber"  => $order->phone,
                    "emailAddress" => $order->email
                ]
            ]
        ];

        // JSON string pro hash + request
        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $headers = $this->makeAuthHeaders(
            $jsonBody,
            $apiToken,
            $secretKey,
            $timestamp,
            $nonce
        );

        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->withBody($jsonBody, 'application/json')
            ->post($urlService);

        if ($response->failed()) {
            return response()->json([
                'error' => '❌ Balíkovna API error',
                'body'  => $response->body()
            ], 500);
        }

        $json = $response->json();
        $parcelCode = $json['responseHeader']['resultParcelData'][0]['parcelCode'] ?? null;
        if (!$parcelCode) {
            return response()->json(['error' => '❌ Balíkovna nevrátila kód zásilky.'], 500);
        }

        // 2. Získání štítku
        $printBody = [
            "printingHeader" => [
                "customerID"      => $customerId,
                "contractNumber"  => $contractNumber,
                "idForm"          => 101,
                "shiftHorizontal" => 0,
                "shiftVertical"   => 0,
                "position"        => 1
            ],
            "printingData" => [$parcelCode]
        ];

        // JSON string pro hash + request
        $jsonPrintBody = json_encode($printBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $printHeaders = $this->makeAuthHeaders(
            $jsonPrintBody,
            $apiToken,
            $secretKey,
            $timestamp,
            $nonce
        );

        $printResp = Http::withHeaders($printHeaders)
            ->withoutVerifying()
            ->withBody($jsonPrintBody, 'application/json')
            ->post($urlPrinting);

        if ($printResp->failed()) {
            return response()->json([
                'error' => '❌ Balíkovna štítek error',
                'body'  => $printResp->body()
            ], 500);
        }

        $labelBase64 = $printResp->json('printingDataResult') ?? null;
        if (!$labelBase64) {
            return response()->json(['error' => '❌ Balíkovna nevrátila štítek.'], 500);
        }

        $pdfLabel = base64_decode($labelBase64);

        // Uložit tracking do DB
        if (!$order->tracking_number) {
            $order->tracking_number = $parcelCode;
            $order->save();
        }

        return response($pdfLabel, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="balikovna-label.pdf"');
    }




    /**
     * Pomocná metoda: UUIDv4
     */
    private function generateUUIDv4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Pomocná metoda: Hlavičky pro autentizaci
     */
    private function makeAuthHeaders(string $payload, string $apiToken, string $secretKey, int $timestamp, string $nonce)
    {
        $sha256Payload = hash('sha256', $payload);
        $signatureString = "$sha256Payload;$timestamp;$nonce";
        $signatureHash = hash_hmac('sha256', $signatureString, $secretKey, true);
        $signatureBase64 = base64_encode($signatureHash);

        return [
            "Api-Token"                   => $apiToken,
            "Authorization-Timestamp"     => $timestamp,
            "Authorization-content-SHA256" => $sha256Payload,
            "Authorization"               => "CP-HMAC-SHA256 nonce=\"$nonce\" signature=\"$signatureBase64\"",
            "Content-Type"                => "application/json;charset=UTF-8"
        ];
    }

    public function waitLabel(string $token, Request $request)
    {
        $carrier = $request->query('carrier');

        if (!$carrier) {
            return response()->json(['error' => 'Chybí parametr carrier!'], 400);
        }

        $order = \App\Models\Order::where('public_token', $token)->firstOrFail();

        return view('labels.wait_label', [
            'order'   => $order,
            'carrier' => strtolower($carrier),
        ]);
    }
}
