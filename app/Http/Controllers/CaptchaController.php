<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    public function generate()
    {
        $code = strtoupper(Str::random(5));
        session(['captcha_code' => $code]);

        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bg = imagecolorallocate($image, 255, 255, 255); // White background
        $text_color = imagecolorallocate($image, 37, 99, 235); // Blue #2563EB
        $line_color = imagecolorallocate($image, 200, 200, 200); // Light gray

        imagefill($image, 0, 0, $bg); // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Add random lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
        }

        // Add text
        $font_size = 20;
        // Centering text roughly
        $x = ($width - (imagefontwidth(5) * strlen($code) * 2)) / 2;
        $y = ($height - imagefontheight(5)) / 2;

        // Using built-in font for simplicity (no external ttf)
        imagestring($image, 5, $x, $y, $code, $text_color);

        // Output image as Laravel Response
        ob_start();
        imagepng($image);
        $buffer = ob_get_clean();
        imagedestroy($image);

        return response($buffer)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
