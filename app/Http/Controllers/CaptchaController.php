<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class CaptchaController extends Controller
{
    /**
     * Generate a more readable and secure CAPTCHA image.
     *
     * Improvements over previous version:
     * - Excludes confusing characters (0, O, 1, I, L)
     * - Better visual noise (dots + arcs) instead of just lines
     * - Larger font and better centering
     * - Colored background with subtle gradient
     * - Wave distortion for anti-OCR
     */
    public function generate()
    {
        $code = $this->generateReadableCode(5);
        session(['captcha_code' => $code]);

        $width = 160;
        $height = 56;
        $image = imagecreatetruecolor($width, $height);

        // Background: subtle gradient
        $bgStart = imagecolorallocate($image, 245, 247, 250);
        $bgEnd   = imagecolorallocate($image, 232, 236, 243);

        for ($y = 0; $y < $height; $y++) {
            $r = intval(245 + (232 - 245) * ($y / $height));
            $g = intval(247 + (236 - 247) * ($y / $height));
            $b = intval(250 + (243 - 250) * ($y / $height));
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Random noise dots
        for ($i = 0; $i < 120; $i++) {
            $dotColor = imagecolorallocate($image, rand(180, 220), rand(180, 220), rand(180, 220));
            imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
        }

        // Random arcs (curved lines)
        for ($i = 0; $i < 4; $i++) {
            $arcColor = imagecolorallocate($image, rand(160, 200), rand(160, 200), rand(160, 200));
            imagearc(
                $image,
                rand(0, $width),
                rand(0, $height),
                rand(60, 120),
                rand(20, 50),
                rand(0, 180),
                rand(180, 360),
                $arcColor
            );
        }

        // Text rendering
        $textColor = imagecolorallocate($image, 37, 99, 235); // Blue #2563EB
        $shadowColor = imagecolorallocate($image, 200, 210, 230);

        $fontSize = 5; // built-in font size (5 is largest)
        $charWidth = imagefontwidth($fontSize);
        $charHeight = imagefontheight($fontSize);

        $totalTextWidth = $charWidth * strlen($code);
        $startX = intval(($width - $totalTextWidth) / 2);
        $startY = intval(($height - $charHeight) / 2);

        // Add wave effect by offsetting Y per character
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $x = $startX + ($i * $charWidth);
            // Slight wave: characters go up and down
            $y = $startY + intval(sin($i * 1.2) * 3);

            // Shadow
            imagestring($image, $fontSize, $x + 1, $y + 1, $char, $shadowColor);
            // Main text
            imagestring($image, $fontSize, $x, $y, $char, $textColor);
        }

        // Thin border
        $borderColor = imagecolorallocate($image, 200, 210, 225);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        ob_start();
        imagepng($image);
        $buffer = ob_get_clean();
        imagedestroy($image);

        return response($buffer)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generate a random code excluding visually similar characters.
     *
     * @param int $length
     * @return string
     */
    private function generateReadableCode(int $length): string
    {
        // Exclude: 0 (zero), O (letter O), 1 (one), I (upper i), L (lower l)
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $code;
    }
}
