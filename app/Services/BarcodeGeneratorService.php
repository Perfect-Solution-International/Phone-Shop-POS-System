<?php

namespace App\Services;

class BarcodeGeneratorService
{
    /**
     * Code 128 character encodings (widths of bars and spaces).
     * 107 characters total: 0-106.
     * Each pattern is 6 digits representing the widths of B1, S1, B2, S2, B3, S3.
     * Stop code (106) has 7 digits: B1, S1, B2, S2, B3, S3, B4.
     */
    protected static array $code128Patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',                                // 100-106
    ];

    /**
     * Generate an SVG Code128 barcode string.
     */
    public function generateCode128Svg(string $text, int $height = 42, float $barWidth = 1.6): string
    {
        $text = trim($text);
        if ($text === '') {
            $text = '000000';
        }

        // Start B symbol is 104
        $symbols = [104];
        $checksum = 104;

        $length = strlen($text);
        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($text[$i]);
            // Code Set B: ASCII 32 to 127
            $val = $ascii >= 32 && $ascii <= 126 ? $ascii - 32 : 0;
            $symbols[] = $val;
            $checksum += $val * ($i + 1);
        }

        $checksumSymbol = $checksum % 103;
        $symbols[] = $checksumSymbol;
        $symbols[] = 106; // Stop code

        // Build bars
        $bars = [];
        foreach ($symbols as $symIndex => $sym) {
            $pattern = self::$code128Patterns[$sym] ?? self::$code128Patterns[0];
            $patLen = strlen($pattern);
            for ($p = 0; $p < $patLen; $p++) {
                $isBar = ($p % 2 === 0);
                $width = (int) $pattern[$p];
                $bars[] = ['isBar' => $isBar, 'width' => $width];
            }
        }

        // Calculate total module width
        $totalModules = 0;
        foreach ($bars as $b) {
            $totalModules += $b['width'];
        }

        $quietZone = 10;
        $svgWidth = ($totalModules + ($quietZone * 2)) * $barWidth;
        $svgHeight = $height;

        $currentX = $quietZone * $barWidth;
        $rects = '';

        foreach ($bars as $b) {
            $w = $b['width'] * $barWidth;
            if ($b['isBar']) {
                $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#000000" />', $currentX, $w, $svgHeight);
            }
            $currentX += $w;
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %.2f %d" width="%.2f" height="%d" style="display:block;margin:0 auto;max-width:100%%;height:auto;">%s</svg>',
            $svgWidth,
            $svgHeight,
            $svgWidth,
            $svgHeight,
            $rects
        );
    }
}

