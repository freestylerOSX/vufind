<?php
/**
 * Dynamic Book Cover Generator
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Cover_Generator
 * @author   Chris Hallberg <crhallberg@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/use_of_external_content Wiki
 */
namespace VuFind\Cover;

/**
 * Dynamic Book Cover Generator
 *
 * @category VuFind2
 * @package  Cover_Generator
 * @author   Chris Hallberg <crhallberg@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/use_of_external_content Wiki
 */
class Generator
{
    /**
     * Style configuration
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Base color used to fill initially created image.
     *
     * @var int
     */
    protected $baseColor;

    /**
     * Title's fill color
     *
     * @var int
     */
    protected $titleFillColor;

    /**
     * Title's border color
     *
     * @var int
     */
    protected $titleBorderColor;

    /**
     * Author's fill color
     *
     * @var int
     */
    protected $authorFillColor;

    /**
     * Author's border color
     *
     * @var int
     */
    protected $authorBorderColor;

    /**
     * Base for image
     *
     * @var resource
     */
    protected $im;

    /**
     * Constructor
     *
     * @param \VuFindTheme\ThemeInfo $themeTools For font loading
     * @param array                  $settings   Overwrite styles
     */
    public function __construct($themeTools, $settings = [])
    {
        $this->themeTools = $themeTools;
        $default = [
            'mode'         => 'grid',
            'authorFont'   => 'DroidSerif-Bold.ttf',
            'fontSize'     => 7,
            'lightness'    => 220,
            'maxLines'     => 5,
            'minFontSize'  => 5,
            'saturation'   => 80,
            'size'         => 84,
            'textAlign'    => 'center',
            'titleFont'    => 'DroidSerif-Bold.ttf',
            'topPadding'   => 19,
            'bottomPadding' => 3,
            'wrapWidth'    => 80,
            'titleFillColor'  => 'black',
            'titleBorderColor'  => 'none',
            'authorFillColor' => 'white',
            'authorBorderColor' => 'black',
            'baseColor' => 'white',
            'accentColor' => 'random',
        ];
        foreach ($settings as $i => $setting) {
            $default[$i] = $setting;
        }
        $default['authorFont'] = $this->fontPath($default['authorFont']);
        $default['titleFont']  = $this->fontPath($default['titleFont']);
        $this->settings = (object) $default;
        $this->initImage();
        $this->initColors();
    }

    /**
     * Initialize colors to be used in the image.
     *
     * @return void
     */
    protected function initColors()
    {
        $this->baseColor = $this->getColor($this->settings->baseColor);
        $this->titleFillColor = $this->getColor($this->settings->titleFillColor);
        $this->titleBorderColor = $this->getColor($this->settings->titleBorderColor);
        $this->authorFillColor = $this->getColor($this->settings->authorFillColor);
        $this->authorBorderColor = $this->getColor(
            $this->settings->authorBorderColor
        );
    }

    /**
     * Initialize the image in the object.
     *
     * @return void
     */
    protected function initImage()
    {
        // Create image
        $this->im = imagecreate($this->settings->size, $this->settings->size);
        if (!$this->im) {
            throw new \Exception("Cannot Initialize new GD image stream");
        }
    }

    /**
     * Clear the resources associated with the image in the object.
     *
     * @return void
     */
    protected function destroyImage()
    {
        imagedestroy($this->im);
    }

    /**
     * Render the contents of the image in the object to a PNG; return as string.
     *
     * @return string
     */
    protected function renderPng()
    {
        ob_start();
        imagepng($this->im);
        $img = ob_get_contents();
        ob_end_clean();
        return $img;
    }

    /**
     * Check and allocates color
     *
     * @param string $color Legal color name from HTML4
     *
     * @return allocated color
     */
    protected function getColor($color)
    {
        switch (strtolower($color)){
        case 'black':
            return imagecolorallocate($this->im, 0, 0, 0);
        case 'silver':
            return imagecolorallocate($this->im, 192, 192, 192);
        case 'gray':
            return imagecolorallocate($this->im, 128, 128, 128);
        case 'white':
            return imagecolorallocate($this->im, 255, 255, 255);
        case 'maroon':
            return imagecolorallocate($this->im, 128, 0, 0);
        case 'red':
            return imagecolorallocate($this->im, 255, 0, 0);
        case 'purple':
            return imagecolorallocate($this->im, 128, 0, 128);
        case 'fuchsia':
            return imagecolorallocate($this->im, 255, 0, 255);
        case 'green':
            return imagecolorallocate($this->im, 0, 128, 0);
        case 'lime':
            return imagecolorallocate($this->im, 0, 255, 0);
        case 'olive':
            return imagecolorallocate($this->im, 128, 128, 0);
        case 'yellow':
            return imagecolorallocate($this->im, 255, 255, 0);
        case 'navy':
            return imagecolorallocate($this->im, 0, 0, 128);
        case 'blue':
            return imagecolorallocate($this->im, 0, 0, 255);
        case 'teal':
            return imagecolorallocate($this->im, 0, 128, 128);
        case 'aqua':
            return imagecolorallocate($this->im, 0, 255, 255);
        default:
            if (substr($color, 0, 1) == '#' && strlen($color) == 7) {
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                return imagecolorallocate($this->im, $r, $g, $b);
            }
            return false;
        }
    }

    /**
     * Generates a dynamic cover image from elements of the item
     *
     * @param string $title      Title of the book
     * @param string $author     Author of the book
     * @param string $callnumber Callnumber of the book
     *
     * @return string contents of image file
     */
    public function generate($title, $author, $callnumber = null)
    {
        switch (strtolower($this->settings->mode)) {
        case 'solid':
            $this->generateSolid($title, $author, $callnumber);
        case 'grid':
        default:
            $this->generateGrid($title, $author, $callnumber);
        }

        $png = $this->renderPng();
        $this->destroyImage();
        return $png;
    }

    /**
     * Generate an accent color from a seed value.
     *
     * @param int $seed Seed value
     *
     * @return int
     */
    protected function getAccentColor($seed)
    {
        // Number to color, hsb to control saturation and lightness
        if ($this->settings->accentColor == 'random') {
            return $this->makeHSBColor(
                $seed % 256,
                $this->settings->saturation,
                $this->settings->lightness
            );
        }
        return $this->getColor($this->settings->accentColor);
    }

    /**
     * Generates a solid color background, ala Google
     *
     * @param string $title      Title of the book
     * @param string $author     Author of the book
     * @param string $callnumber Callnumber of the book
     *
     * @return void
     */
    protected function generateSolid($title, $author, $callnumber)
    {
        $box  = $this->settings->size / 8;

        // Generate seed from callnumber, title back up
        $seed = $this->createSeed($title, $callnumber);

        // Fill solid color
        imagefilledrectangle(
            $this->im,
            0,
            0,
            $this->settings->size,
            $this->settings->size,
            $this->getAccentColor($seed)
        );

        $this->drawTitle($title, $box);
        $this->drawAuthor($author);
    }

    /**
     * Generates a grid of colors as primary feature
     *
     * @param string $title      Title of the book
     * @param string $author     Author of the book
     * @param string $callnumber Callnumber of the book
     *
     * @return void
     */
    protected function generateGrid($title, $author, $callnumber)
    {
        // Set up common variables
        $half = $this->settings->size / 2;
        $box  = $this->settings->size / 8;

        // Generate seed from callnumber, title back up
        $seed = $this->createSeed($title, $callnumber);
        // Render the grid
        $pattern = $this->createPattern($seed);
        $this->render($pattern, $this->getAccentColor($seed), $half, $box);

        if (null !== $title) {
            $this->drawTitle($title, $box);
        }
        if (null !== $author) {
            $this->drawAuthor($author);
        }
    }

    /**
     * Generates a dynamic cover image from elements of the book
     *
     * @param string $title      Title of the book
     * @param string $callnumber Callnumber of the book
     *
     * @return int unique number for this record
     */
    protected function createSeed($title, $callnumber)
    {
        // Turn callnumber into number
        if (null == $callnumber) {
            $callnumber = $title;
        }
        if (null !== $callnumber) {
            $cv = 0;
            for ($i = 0;$i < strlen($callnumber);$i++) {
                $cv += ord($callnumber[$i]);
            }
            return $cv;
        } else {
            // If no callnumber, random
            return ceil(rand(pow(2, 4), pow(2, 32)));
        }
    }

    /**
     * Turn number into pattern
     *
     * @param int $seed Seed used to generate the pattern
     *
     * @return string binary string describing a quarter of the pattern
     */
    protected function createPattern($seed)
    {
        // Convert to binary
        $bc = decbin($seed);
        // If we have less that a half of a quarter
        if (strlen($bc) < 8) {
            // Rotate square of the first 4 into a 4x2
            // Simulate matrix rotation on string
            $bc = substr($bc, 0, 3)
                . substr($bc, 0, 1)
                . substr($bc, 2, 2)
                . substr($bc, 3, 1)
                . substr($bc, 1, 1);
        }
        // If we have less than a quarter
        if (strlen($bc) < 16) {
            // Rotate the first 8 as a 4x2 into a 4x4
            $bc .= strrev($bc);
        }
        return $bc;
    }

    /**
     * Render title in wrapped, black text with white border
     *
     * @param string $title      Title to write
     * @param int    $lineHeight Pixels we move down each line
     *
     * @return void
     */
    protected function drawTitle($title, $lineHeight)
    {
        $words = explode(' ', $title);
        // Wrap words into image
        // Add words until off image, go back and print
        $line = '';
        $lineCount = 0;
        $i = 0;
        while ($i < count($words) && $lineCount < $this->settings->maxLines - 1) {
            $pline = $line;
            // Format
            $text = $words[$i];
            $line .= $text . ' ';
            $textWidth = $this->textWidth(
                rtrim($line, ' '),
                $this->settings->titleFont,
                $this->settings->fontSize
            );
            if ($textWidth > $this->settings->wrapWidth) {
                // Print black with white border
                $this->drawText(
                    rtrim($pline, ' '),
                    $this->settings->topPadding + $lineHeight * $lineCount,
                    $this->settings->titleFont,
                    $this->settings->fontSize,
                    $this->titleFillColor,
                    $this->titleBorderColor
                );
                $line = $text . ' ';
                $lineCount++;
            }
            $i++;
        }
        // Print the last words
        $this->drawText(
            rtrim($line, ' '),
            $this->settings->topPadding + $lineHeight * $lineCount,
            $this->settings->titleFont,
            $this->settings->fontSize,
            $this->titleFillColor,
            $this->titleBorderColor
        );
        // Add ellipses if we've truncated
        if ($i < count($words) - 1) {
            $this->drawText(
                '...',
                $this->settings->topPadding
                + $this->settings->maxLines * $lineHeight,
                $this->settings->titleFont,
                $this->settings->fontSize + 1,
                $this->titleFillColor,
                $this->titleBorderColor
            );
        }
    }

    /**
     * Render author at bottom in wrapped, white text with black border
     *
     * @param string $author Author to write
     *
     * @return void
     */
    protected function drawAuthor($author)
    {
        // Scale author to fit by incrementing fontsizes down
        $fontSize = $this->settings->fontSize;
        do {
            $fontSize--;
            $textWidth = $this->textWidth(
                $author,
                $this->settings->authorFont,
                $fontSize
            );
        } while ($textWidth > $this->settings->wrapWidth &&
              $fontSize > $this->settings->minFontSize
          );
        // Too small to read? Align left
        $textWidth = $this->textWidth(
            $author,
            $this->settings->authorFont,
            $fontSize
        );
        $align = $textWidth > $this->settings->size
            ? 'left'
            : null;
        $this->drawText(
            $author,
            $this->settings->size - $this->settings->bottomPadding,
            $this->settings->authorFont,
            $fontSize,
            $this->authorFillColor,
            $this->authorBorderColor,
            $align
        );
    }

    /**
     * Find font in the theme folder
     *
     * @param string $font Font_name.ttf
     *
     * @return string file path
     */
    protected function fontPath($font)
    {
        // Check all supported image formats:
        $filenames = ['css/font/' . $font];
        $fileMatch = $this->themeTools->findContainingTheme($filenames, true);
        return empty($fileMatch) ? false : $fileMatch;
    }

    /**
     * Returns the width a string would render to
     *
     * @param string $text Text to test
     * @param string $font Full font path
     * @param string $size Size of the font
     *
     * @return string file path
     */
    protected function textWidth($text, $font, $size)
    {
        $p = imagettfbbox($size, 0, $font, $text);
        return $p[2] - $p[0];
    }

    /**
     * Simulate outlined text
     *
     * @param string  $text     Text to render
     * @param int     $y        Top position
     * @param string  $font     Full path to font
     * @param int     $fontSize Size of the font
     * @param GCColor $mcolor   Main text color
     * @param GCColor $scolor   Secondary border color
     * @param string  $align    'left','center','right'
     *
     * @return void
     */
    protected function drawText($text, $y, $font, $fontSize, $mcolor,
        $scolor = false, $align = null
    ) {
        $textWidth = $this->textWidth(
            $text,
            $font,
            $fontSize
        );
        if ($textWidth > $this->settings->size) {
            $align = 'left';
        }
        if (null == $align) {
            $align = $this->settings->textAlign;
        }
        if ($align == 'left') {
            $x = 0;
        }
        if ($align == 'center') {
            $x = ($this->settings->size - $textWidth) / 2;
        }
        if ($align == 'right') {
            $x = $this->settings->size - $textWidth;
        }

        // Generate 5 lines of text, 4 offset in a border color
        if ($scolor) {
            imagettftext(
                $this->im, $fontSize, 0, $x,   $y + 1, $scolor, $font, $text
            );
            imagettftext(
                $this->im, $fontSize, 0, $x,   $y - 1, $scolor, $font, $text
            );
            imagettftext(
                $this->im, $fontSize, 0, $x + 1, $y,   $scolor, $font, $text
            );
            imagettftext(
                $this->im, $fontSize, 0, $x - 1, $y,   $scolor, $font, $text
            );
        }
        // 1 centered in main color
        imagettftext($this->im, $fontSize, 0, $x,   $y,   $mcolor, $font, $text);
    }

    /**
     * Convert 16 long binary string to 8x8 color grid
     * Reflects vertically and horizontally
     *
     * @param string  $bc    Binary string of pattern
     * @param GCColor $color Fill color
     * @param int     $half  Half the size, shortcut for math
     * @param int     $box   Box size
     *
     * @return void
     */
    protected function render($bc, $color, $half, $box)
    {
        $bc = str_split($bc);
        for ($k = 0;$k < 4;$k++) {
            $x = $k % 2   ? $half : $half - $box;
            $y = $k / 2 < 1 ? $half : $half - $box;
            $u = $k % 2   ? $box : -$box;
            $v = $k / 2 < 1 ? $box : -$box;
            for ($i = 0;$i < 16;$i++) {
                if ($bc[$i] == "1") {
                    imagefilledrectangle(
                        $this->im, $x, $y, $x + $box - 1, $y + $box - 1, $color
                    );
                }
                $x += $u;
                if ($x >= $this->settings->size || $x < 0) {
                    $x = $k % 2 ? $half : $half - $box;
                    $y += $v;
                }
            }
        }
        //imagefilledrectangle($this->im,0,$size-11,$size-1,$size,$color);
    }

    /**
     * Using HSB allows us to control the contrast while allowing randomness
     *
     * @param int $h Hue (0-255)
     * @param int $s Saturation (0-100)
     * @param int $v Lightness (0-100)
     *
     * @return GCColor
     */
    protected function makeHSBColor($h, $s, $v)
    {
        $s /= 256.0;
        if ($s == 0.0) {
            return imagecolorallocate($this->im, $v, $v, $v);
        }
        $h /= (256.0 / 6.0);
        $i = floor($h);
        $f = $h - $i;
        $p = (int)($v * (1.0 - $s));
        $q = (int)($v * (1.0 - $s * $f));
        $t = (int)($v * (1.0 - $s * (1.0 - $f)));
        switch($i) {
        case 0:
            return imagecolorallocate($this->im, $v, $t, $p);
        case 1:
            return imagecolorallocate($this->im, $q, $v, $p);
        case 2:
            return imagecolorallocate($this->im, $p, $v, $t);
        case 3:
            return imagecolorallocate($this->im, $p, $q, $v);
        case 4:
            return imagecolorallocate($this->im, $t, $p, $v);
        default:
            return imagecolorallocate($this->im, $v, $p, $q);
        }
    }
}
