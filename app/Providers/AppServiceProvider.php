<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SimpleSoftwareIO\QrCode\Generator;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nastavíme QR Code generator s GD backendem (SVG funguje i v DomPDF)
        $this->app->singleton('qrCode', function () {
            return new Generator(
                new ImageRenderer(
                    new RendererStyle(200),
                    new SvgImageBackEnd() // 👈 SVG backend místo Imagick
                )
            );
        });
    }

    public function boot(): void {}
}
