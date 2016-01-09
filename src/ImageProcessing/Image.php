<?php

namespace PetrKnap\Utils\ImageProcessing;

use PetrKnap\Php\Image\Image as PhpImage;

/**
 * Class designed to simplify the image processing in PHP
 *
 * This class provides methods for loading, transforming, storing and displaying image data. The code is being developed
 * since 2008. If you need to add method for specific functionality, please send your proposal or modified source code
 * to "the developers e-mail":[mailto:dev%40petrknap%2Ecz?subject=Image%2Eclass%2Ephp].
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2008-09-04
 * @category ImageProcessing
 * @package  PetrKnap\Utils\ImageProcessing
 * @version  9.1
 * @license  https://github.com/petrknap/utils/blob/master/LICENSE MIT
 * @homepage http://dev.petrknap.cz/ImageProcessing/Image.php.html
 * @example  ImageTest.php Test cases
 * @deprecated extracted to https://github.com/petrknap/php-images
 *
 * @property string PathToFile Path to image file
 * @property int Width Width of image in pixels
 * @property int Height Height of image in pixels
 * @property int Type Type of image (constants `GIF`, `JPG`, `PNG` and `BMP`)
 * @property resource Resource RAW image resource
 * @property int BackgroundColor Background color in hexadecimal `0xAARRGGBB` (ARGB) format
 * @property int TransparentColor Transparent color in hexadecimal `0xAARRGGBB` (ARGB) format
 * @property int JpgQuality JPG quality in percents (from 1 to 100)
 *
 * @change 9.1  Removed property `Image`:[#property_Image]
 * @change 9.1  Renamed method `setImage` to `setResource`:[#method_setResource]
 * @change 9.1  Added method `crop`:[#method_crop]
 * @change 9.0  Removed backward compatibility with versions 8.*
 * @change 9.0  Now throws `ImageException` instead of `\Exception`
 * @change 9.0  Added method `__toString`:[#method___toString]
 * @change 9.0  Added property `Resource`:[#property_Resource]
 * @change 8.11 Changed licensing from "MS-PL":[http://opensource.org/licenses/ms-pl.html] to "MIT":[https://github.com/petrknap/utils/blob/master/LICENSE]
 * @change 8.11 Moved to `PetrKnap\Utils\ImageProcessing`
 * @change 8.11 Fully translated PhpDocs
 * @change 8.10 Added method `fromResource`:[#method_fromResource]
 * @change 8.10 Added method `setImage`:[#method_setImage]
 * @change 8.9  Added method `rotate`:[#method_rotate]
 * @change 8.9  Added method `rotateLeft`:[#method_rotateLeft]
 * @change 8.9  Added method `rotateRight`:[#method_rotateRight]
 */
class Image extends PhpImage
{
    // Use PetrKnap\Php\Image\Image instead
}
