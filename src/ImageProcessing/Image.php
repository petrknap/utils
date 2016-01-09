<?php

namespace PetrKnap\Utils\ImageProcessing;

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
class Image
{
    #region Attributes
    private $pathToFile;
    private $width;
    private $height;
    private $type;
    private $resource;
    private $backgroundColor = 0x00FFFFFF;
    private $transparentColor = null;
    private $jpgQuality = 85;

    /**
     * @const int Positions
     */
    const
        LeftTop = 1,
        CenterTop = 2,
        RightTop = 3,
        LeftCenter = 4,
        CenterCenter = 5,
        RightCenter = 6,
        LeftBottom = 7,
        CenterBottom = 8,
        RightBottom = 9;

    /**
     * @const int Image types
     */
    const
        GIF = IMAGETYPE_GIF,
        JPG = IMAGETYPE_JPEG,
        PNG = IMAGETYPE_PNG,
        BMP = IMAGETYPE_WBMP;

    #endregion

    #region Base methods
    /**
     * Creates empty instance
     */
    private function __construct() { }

    /**
     * Automatically frees RAM
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch(\Exception $ignore) {
            // Ignore exceptions
        }
    }

    /**
     * Converts object into string
     *
     * @return string
     */
    public function __toString() {
        return sprintf("%s(%s, width %u px, height %u px)", get_class($this), $this->resource, $this->width, $this->height);
    }

    /**
     * Creates new Image object from image file
     *
     * @param string $pathToFile Path to image file
     * @return self
     */
    public static function fromFile($pathToFile) {
        $newImage = new self();
        $newImage->open($pathToFile);
        return $newImage;
    }

    /**
     * Creates new Image object from image resource
     *
     * @param resource $resource Image resource
     * @throws ImageException
     * @return self
     */
    public static function fromResource($resource) {
        $newImage = new self();
        try {
            $newImage->setResource($resource);
        } catch(\Exception $e) {
            throw new ImageException($e->getMessage(), ImageException::UnsupportedFormatException, $e);
        }
        return $newImage;
    }

    /**
     * Creates copy of Image object
     *
     * @param self $image Image object
     * @return self
     */
    public static function fromImage(self $image) {
        $newImage = clone $image;
        return $newImage;
    }

    /**
     * Sets image resource as new image
     *
     * @param resource $resource image resource
     */
    public function setResource($resource) {
        $this->resource = $resource;
        $this->width = imagesx($resource);
        $this->height = imagesy($resource);
    }

    /**
     * Loads image file to RAM
     *
     * @param string $pathToFile Path to loaded file
     * @throws ImageException
     */
    private function open($pathToFile)
    {
        $this->pathToFile = $pathToFile;
        if (!file_exists($this->pathToFile)) {
            throw new ImageException("File " . ($this->pathToFile) . " not found.", ImageException::AccessException);
        }
        $tmpImageSize = getimagesize($this->pathToFile);
        $this->width = (int)$tmpImageSize[0];
        $this->height = (int)$tmpImageSize[1];
        $this->type = (int)$tmpImageSize[2];
        switch ($this->type) {
            case self::GIF:
                $this->resource = imagecreatefromgif($this->pathToFile);
                break;
            case self::JPG:
                $this->resource = imagecreatefromjpeg($this->pathToFile);
                break;
            case self::PNG:
                $this->resource = imagecreatefrompng($this->pathToFile);
                break;
            case self::BMP:
                $this->resource = imagecreatefromwbmp($this->pathToFile);
                break;
            default:
                throw new ImageException("Unknown type of file " . ($this->pathToFile) . ".", ImageException::UnsupportedFormatException);
                break;
        }
    }

    /**
     * Changes the image resolution
     *
     * @param int $width  New width in pixels
     * @param int $height New height in pixels
     */
    public function resize($width, $height)
    {
        $width = (int)$width;
        $height = (int)$height;
        $tmpImage = imagecreatetruecolor($width, $height);
        imagefilledrectangle($tmpImage, 0, 0, $width, $height, $this->backgroundColor);
        imagecopyresampled($tmpImage, $this->resource, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->width = $width;
        $this->height = $height;
        $this->resource = $tmpImage;
    }

    /**
     * Semi-automatic image resize
     *
     * New height is computed from aspect ratio.
     *
     * @see resize()
     * @param int $width New width in pixels
     */
    public function resizeW($width)
    {
        $tmpHeight = $width / $this->width * $this->height;
        $this->resize((int)$width, (int)$tmpHeight);
    }

    /**
     * Semi-automatic image resize
     *
     * New width is computed from aspect ratio.
     *
     * @see resize()
     * @param int $height New height in pixels
     */
    public function resizeH($height)
    {
        $tmpWidth = $height / $this->height * $this->width;
        $this->resize((int)$tmpWidth, (int)$height);
    }

    /**
     * Rotates an image with a given angle
     *
     * @param float $angle Rotation angle in degrees
     */
    public function rotate($angle)
    {
        $this->setResource(imagerotate($this->resource, $angle , $this->BackgroundColor));
    }

    /**
     * Rotates image left (90°)
     */
    public function rotateLeft() {
        $this->rotate(90);
    }

    /**
     * Rotates image right (270°)
     */
    public function rotateRight() {
        $this->rotate(270);
    }

    /**
     * Crops image via rectangle
     *
     * @param array $rectangle
     * @throws ImageException If couldn't crop image.
     */
    public function crop(array $rectangle) {
        $croppedImage = @imagecrop($this->resource, $rectangle);
        if($croppedImage === false) {
            $rectangleAsString = "{";
            foreach($rectangle as $key => $value) {
                $rectangleAsString .= "\"{$key}\" => {$value}, ";
            }
            $rectangleAsString = preg_replace("/, $/", "}", $rectangleAsString);
            throw new ImageException(
                "Can not crop image {$this} via rectangle {$rectangleAsString}.",
                ImageException::GenericException
            );
        }
        $this->resource = $croppedImage;
        $this->width = $rectangle["width"];
        $this->height = $rectangle["height"];
    }

    /**
     * Joins images together
     *
     * Puts second image on this image at specific position.
     *
     * Acceptable positions are:
     *
     * .[table table-bordered]
     * | `self::LeftTop`    | `self::CenterTop`    | `self::RightTop`    |
     * | `self::LeftCenter` | `self::CenterCenter` | `self::RightCenter` |
     * | `self::LeftBottom` | `self::CenterBottom` | `self::RightBottom` |
     *
     *
     * @param self $secondImage Foreground Image object
     * @param int $position Predefined position of foreground Image object (default: self::RightBottom)
     * @throws ImageException If couldn't find position.
     */
    public function join(self $secondImage, $position = self::RightBottom)
    {
        if($secondImage->Width > $this->Width || $secondImage->Height > $this->Height) {
            throw new ImageException("Cannot insert bigger {$secondImage} into smaller {$this}.", ImageException::OutOfRangeException);
        }
        switch ($position) {
            case self::LeftTop:
                $x = 0; // left
                $y = 0; // top
                break;
            case self::CenterTop:
                $x = $this->width / 2 - $secondImage->Width / 2; // center
                $y = 0; // top
                break;
            case self::RightTop:
                $x = $this->width - $secondImage->Width; // right
                $y = 0; // top
                break;
            case self::LeftCenter:
                $x = 0; // left
                $y = $this->height / 2 - $secondImage->Height / 2; // center
                break;
            case self::CenterCenter:
                $x = $this->width / 2 - $secondImage->Width / 2; // center
                $y = $this->height / 2 - $secondImage->Height / 2; // center
                break;
            case self::RightCenter:
                $x = $this->width - $secondImage->Width; // right
                $y = $this->height / 2 - $secondImage->Height / 2; // center
                break;
            case self::LeftBottom:
                $x = 0; // left
                $y = $this->height - $secondImage->Height; // bottom
                break;
            case self::CenterBottom:
                $x = $this->width / 2 - $secondImage->Width / 2; // center
                $y = $this->height - $secondImage->Height; // bottom
                break;
            case self::RightBottom:
                $x = $this->width - $secondImage->Width; // right
                $y = $this->height - $secondImage->Height; // bottom
                break;
            default:
                throw new ImageException("Position " . $position . " not found.", ImageException::OutOfRangeException);
                break;
        }
        imagecopy($this->resource, $secondImage->Resource, $x, $y, 0, 0, $secondImage->Width, $secondImage->Height);
    }

    /**
     * Sends image resource to standard output
     *
     * Content-Type header is generated automatically.
     *
     * @see $type
     * @throws ImageException If couldn't find image type.
     */
    public function show()
    {
        switch ($this->type) {
            case self::GIF:
                header("Content-Type: image/gif");
                imagegif($this->resource);
                break;
            case self::JPG:
                header("Content-Type: image/jpeg");
                imagejpeg($this->resource, null, $this->jpgQuality);
                break;
            case self::PNG:
                header("Content-Type: image/png");
                imagepng($this->resource);
                break;
            case self::BMP:
                header("Content-Type: image/wbmp");
                imagewbmp($this->resource);
                break;
            default:
                throw new ImageException("Unknown type of file " . ($this->pathToFile) . ".", ImageException::UnsupportedFormatException);
                break;
        }
    }

    /**
     * Saves image resource to file
     *
     * @param string $pathToFile Path to file
     * @param int $type Output type of image
     * @param int $jpgQuality Output JPG quality in percents (from 1 to 100)
     * @throws ImageException If couldn't find image type.
     */
    public function save($pathToFile = null, $type = null, $jpgQuality = null)
    {
        if ($pathToFile === null) $pathToFile = $this->pathToFile;
        if ($type === null) $type = $this->type;
        if ($jpgQuality === null) $jpgQuality = $this->jpgQuality;
        switch ($type) {
            case self::GIF:
                imagegif($this->resource, $pathToFile);
                break;
            case self::JPG:
                imagejpeg($this->resource, $pathToFile, $jpgQuality);
                break;
            case self::PNG:
                imagepng($this->resource, $pathToFile);
                break;
            case self::BMP:
                imagewbmp($this->resource, $pathToFile);
                break;
            default:
                throw new ImageException("Unknown type of file " . ($this->pathToFile) . ".", ImageException::UnsupportedFormatException);
                break;
        }
        $this->pathToFile = $pathToFile;
    }

    /**
     * Deletes image resource from RAM
     */
    public function close()
    {
        @imagedestroy($this->resource);
    }
    #endregion

    #region Getters and setters
    /**
     * Returns property value by name
     *
     * @param string $name Property name
     * @return mixed Property value
     * @throws ImageException If couldn't find property.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'PathToFile':
                return $this->pathToFile;
            case 'Width':
                return $this->width;
            case 'Height':
                return $this->height;
            case 'Type':
                return $this->type;
            case 'Image': // TODO remove Image property
            case 'Resource':
                return $this->resource;
            case 'BackgroundColor':
                return $this->backgroundColor;
            case "TransparentColor":
                return $this->transparentColor;
            case 'JpgQuality':
                return $this->jpgQuality;
            default:
                throw new ImageException("Variable $" . $name . " not found.", ImageException::GenericException);
        }
    }

    /**
     * Sets property value by name
     *
     * @param string $name Property name
     * @param mixed $value Property value
     * @throws ImageException If couldn't access property.
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'Type':
                $this->setType($value);
                break;
            case 'BackgroundColor':
                $this->setBackgroundColor($value);
                break;
            case "TransparentColor":
                $this->setTransparent($value);
                break;
            case 'JpgQuality':
                $this->setJpgQuality($value);
                break;
            case 'PathToFile':
            case 'Width':
            case 'Height':
            case 'Image':
                throw new ImageException("Variable $" . $name . " is readonly.", ImageException::AccessException);
                break;
            default:
                throw new ImageException("Variable $" . $name . " not found.", ImageException::GenericException);
                break;
        }
    }

    /**
     * Sets image type
     *
     * @param int $type
     */
    private function setType($type)
    {
        $this->type = (int)$type;
    }

    /**
     * Sets background color
     *
     * @param int $backgroundColor
     */
    private function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = (int)$backgroundColor;
        $tmpImage = imagecreatetruecolor($this->width, $this->height);
        imagefilledrectangle($tmpImage, 0, 0, $this->width, $this->height, $this->backgroundColor);
        imagecopy($tmpImage, $this->resource, 0, 0, 0, 0, $this->width, $this->height);
        $this->resource = $tmpImage;
    }

    /**
     * Sets transparent color
     *
     * @param int $color
     */
    private function setTransparent($color)
    {
        $this->transparentColor = $color;
        imagecolortransparent($this->resource, (int)$this->transparentColor);
    }

    /**
     * Sets JPG quality
     *
     * @param int $jpgQuality
     * @throws ImageException
     */
    private function setJpgQuality($jpgQuality)
    {
        if($jpgQuality < 1 || $jpgQuality > 100) {
            throw new ImageException("Value must be between 1 and 100.", ImageException::OutOfRangeException);
        }
        $this->jpgQuality = (int)$jpgQuality;
    }
    #endregion
}
