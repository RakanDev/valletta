<?php

namespace App\Support;

use RuntimeException;

/**
 * Turns any product photo into the store standard: 1080x1350 (4:5) WebP.
 *
 *  - close to 4:5 already            -> centre-cropped to fill the frame
 *  - plain studio background         -> whole photo, background colour extended
 *  - squarish lifestyle photo        -> centre-cropped (only the sides are trimmed)
 *  - wide lifestyle photo / chart    -> whole photo on a soft blurred copy of itself
 */
class ImageNormalizer
{
    public const WIDTH = 1080;

    public const HEIGHT = 1350;

    private const CROP_TOLERANCE = 0.12;

    public static function toWebp(string $bytes, int $quality = 84): string
    {
        $src = @imagecreatefromstring($bytes);
        if (! $src) {
            throw new RuntimeException('Unsupported image data.');
        }
        imagepalettetotruecolor($src);
        $src = self::trimBars($src);

        $w = imagesx($src);
        $h = imagesy($src);
        $target = self::WIDTH / self::HEIGHT;
        $ratio = $w / $h;
        [$avg, $plain] = self::border($src, $w, $h);

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        if (abs($ratio - $target) / $target <= self::CROP_TOLERANCE || (! $plain && $ratio <= 1.05)) {
            [$cx, $cy, $cw, $ch] = self::coverBox($w, $h, $target);
            imagecopyresampled($canvas, $src, 0, 0, $cx, $cy, self::WIDTH, self::HEIGHT, $cw, $ch);
        } else {
            if ($plain) {
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...$avg));
            } else {
                self::blurredBackground($canvas, $src, $w, $h, $target);
            }
            $scale = min(self::WIDTH / $w, self::HEIGHT / $h);
            $fw = (int) round($w * $scale);
            $fh = (int) round($h * $scale);
            imagecopyresampled($canvas, $src, intdiv(self::WIDTH - $fw, 2), intdiv(self::HEIGHT - $fh, 2), 0, 0, $fw, $fh, $w, $h);
        }

        ob_start();
        imagewebp($canvas, null, $quality);
        $out = (string) ob_get_clean();
        imagedestroy($src);
        imagedestroy($canvas);

        return $out;
    }

    /** Remove solid black bars (screenshot letterboxing) from the top and bottom. */
    private static function trimBars($img)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $isBar = function (int $y) use ($img, $w): bool {
            $dark = 0;
            $n = 0;
            for ($x = 0; $x < $w; $x += 4) {
                $c = imagecolorat($img, $x, $y);
                $n++;
                if ((($c >> 16) & 255) + (($c >> 8) & 255) + ($c & 255) < 75) {
                    $dark++;
                }
            }

            return $dark / $n >= 0.97;
        };

        $limit = (int) ($h * 0.25);
        $top = 0;
        while ($top < $limit && $isBar($top)) {
            $top++;
        }
        $bottom = 0;
        while ($bottom < $limit && $isBar($h - 1 - $bottom)) {
            $bottom++;
        }
        // Ignore a hairline; only cut real bars, and keep a small safety margin.
        $top = $top >= 6 ? $top + 2 : 0;
        $bottom = $bottom >= 6 ? $bottom + 2 : 0;
        if ($top === 0 && $bottom === 0) {
            return $img;
        }

        $out = imagecrop($img, ['x' => 0, 'y' => $top, 'width' => $w, 'height' => $h - $top - $bottom]);
        imagedestroy($img);

        return $out;
    }

    /** Average border colour and whether the border is a plain (studio) background. */
    private static function border($img, int $w, int $h): array
    {
        $pts = [];
        $step = max(1, intdiv(min($w, $h), 40));
        for ($x = 0; $x < $w; $x += $step) {
            $pts[] = [$x, 2];
            $pts[] = [$x, $h - 3];
        }
        for ($y = 0; $y < $h; $y += $step) {
            $pts[] = [2, $y];
            $pts[] = [$w - 3, $y];
        }

        $sum = [0, 0, 0];
        $cols = [];
        foreach ($pts as [$x, $y]) {
            $c = imagecolorat($img, $x, $y);
            $rgb = [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
            $cols[] = $rgb;
            for ($i = 0; $i < 3; $i++) {
                $sum[$i] += $rgb[$i];
            }
        }
        $n = count($cols);
        $avg = array_map(fn ($s) => (int) round($s / $n), $sum);

        // Share of border samples close to the average colour.
        $close = 0;
        foreach ($cols as $rgb) {
            if (abs($rgb[0] - $avg[0]) + abs($rgb[1] - $avg[1]) + abs($rgb[2] - $avg[2]) < 30) {
                $close++;
            }
        }

        return [$avg, $close / $n >= 0.9];
    }

    private static function blurredBackground($canvas, $src, int $w, int $h, float $target): void
    {
        [$cx, $cy, $cw, $ch] = self::coverBox($w, $h, $target);
        $tiny = imagecreatetruecolor(16, 20);
        imagecopyresampled($tiny, $src, 0, 0, $cx, $cy, 16, 20, $cw, $ch);
        $mid = imagescale($tiny, 216, 270, IMG_BICUBIC);
        for ($i = 0; $i < 10; $i++) {
            imagefilter($mid, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $big = imagescale($mid, self::WIDTH, self::HEIGHT, IMG_BICUBIC);
        imagecopy($canvas, $big, 0, 0, 0, 0, self::WIDTH, self::HEIGHT);
        imagedestroy($tiny);
        imagedestroy($mid);
        imagedestroy($big);
    }

    /** Largest centred box of the target ratio inside a w x h image. */
    private static function coverBox(int $w, int $h, float $target): array
    {
        if ($w / $h > $target) {
            $cw = (int) round($h * $target);

            return [intdiv($w - $cw, 2), 0, $cw, $h];
        }
        $ch = (int) round($w / $target);

        return [0, intdiv($h - $ch, 2), $w, $ch];
    }
}
