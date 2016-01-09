<?php

use PetrKnap\Utils\ImageProcessing\Image;
use PetrKnap\Utils\ImageProcessing\ImageException;

class ImageTest extends PHPUnit_Framework_TestCase
{
    private $inputImage = array(
        "file" => null,
        "image" => null,
        "resource" => null,
        "width" => 180,
        "height" => 40
    );

    private $supportedFormats = array(
        Image::BMP,
        Image::GIF,
        Image::JPG,
        Image::PNG
    );

    private $supportedPositions = array(
        Image::LeftTop,
        Image::LeftBottom,
        Image::LeftCenter,
        Image::CenterTop,
        Image::CenterCenter,
        Image::CenterBottom,
        Image::RightTop,
        Image::RightCenter,
        Image::RightBottom
    );

    public function setUp() {
        $this->inputImage["file"] = __DIR__ . "/ImageTest.png";
        try {
            $this->inputImage["image"] = Image::fromFile($this->inputImage["file"]);
            $this->inputImage["resource"] = $this->inputImage["image"]->Resource;
        } catch (ImageException $ie) {
            $this->fail($ie->getMessage());
        }
    }

    private function checkImage($image) {
        $this->assertEquals($this->inputImage["width"], $image->Width);
        $this->assertEquals($this->inputImage["height"], $image->Height);
    }

    #region Construction
    /**
     * @covers Image::fromFile
     */
    public function testCanBeCreatedFromFile() {
        $image = Image::fromFile($this->inputImage["file"]);

        $this->assertEquals($this->inputImage["file"], $image->PathToFile);
        $this->checkImage($image);

        // Try case: Non-image file
        try {
            Image::fromFile(__FILE__);
            $this->fail("Non-image file doesn't throw exception.");
        } catch(ImageException $ie) {
            $this->assertEquals(ImageException::UnsupportedFormatException, $ie->getCode());
        }

        // Try case: Nonexistent file
        try {
            Image::fromFile(__DIR__ . "/ImageTest.nonexistent.png");
            $this->fail("Nonexistent file doesn't throw exception.");
        } catch(ImageException $ie) {
            $this->assertEquals(ImageException::AccessException, $ie->getCode());
        }
    }

    /**
     * @covers Image::fromImage
     */
    public function testCanBeCreatedFromAnotherImage() {
        $image = Image::fromImage($this->inputImage["image"]);

        $this->assertEquals($this->inputImage["image"]->PathToFile, $image->PathToFile);
        $this->checkImage($image);
    }

    /**
     * @throws ImageException
     * @covers Image::fromResource
     */
    public function testCanBeCreatedFromResource() {
        $image = Image::fromResource($this->inputImage["resource"]);

        $this->checkImage($image);

        // Try case: Non-image resource
        $resource = fopen(__FILE__, "r");
        try {
            Image::fromResource($resource);
            $this->fail("Non-image file doesn't throw exception.");
        } catch(ImageException $ie) {
            $this->assertEquals(ImageException::UnsupportedFormatException, $ie->getCode());
        }
        fclose($resource);
    }
    #endregion

    #region Resizing
    /**
     * @throws ImageException
     * @covers Image::resize
     */
    public function testCanBeUpScaledByWAndH() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resize($this->inputImage["width"] * 2, $this->inputImage["height"] * 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] * 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] * 2, $image->Height, '', 0.75);
    }

    /**
     * @throws ImageException
     * @covers Image::resizeW
     */
    public function testCanBeUpScaledByWOnly() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resizeW($this->inputImage["width"] * 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] * 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] * 2, $image->Height, '', 0.75);
    }

    /**
     * @throws ImageException
     * @covers Image::resizeH
     */
    public function testCanBeUpScaledByHOnly() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resizeH($this->inputImage["height"] * 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] * 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] * 2, $image->Height, '', 0.75);
    }

    /**
     * @throws ImageException
     * @covers Image::resize
     */
    public function testCanBeDownScaledByWAndH() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resize($this->inputImage["width"] / 2, $this->inputImage["height"] / 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] / 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] / 2, $image->Height, '', 0.75);
    }

    /**
     * @throws ImageException
     * @covers Image::resizeW
     */
    public function testCanBeDownScaledByWOnly() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resizeW($this->inputImage["width"] / 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] / 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] / 2, $image->Height, '', 0.75);
    }

    /**
     * @throws ImageException
     * @covers Image::resizeH
     */
    public function testCanBeDownScaledByHOnly() {
        $image = Image::fromImage($this->inputImage["image"]);

        $image->resizeH($this->inputImage["height"] / 2);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($this->inputImage["width"] / 2, $image->Width, '', 0.75);
        $this->assertEquals($this->inputImage["height"] / 2, $image->Height, '', 0.75);
    }
    #endregion

    #region Rotating
    /**
     * @covers Image::rotate
     * @covers Image::rotateLeft
     */
    public function testCanBeRotatedLeft() {
        $imageA = Image::fromImage($this->inputImage["image"]);
        $imageB = Image::fromImage($this->inputImage["image"]);

        $imageA->rotate(90);
        $imageB->rotateLeft();

        ob_start();
        imagepng($imageA->Resource);
        $pngA = ob_get_contents();
        ob_end_clean();

        ob_start();
        imagepng($imageB->Resource);
        $pngB = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($pngA, $pngB);
    }

    /**
     * @covers Image::rotate
     * @covers Image::rotateRight
     */
    public function testCanBeRotatedRight() {
        $imageA = Image::fromImage($this->inputImage["image"]);
        $imageB = Image::fromImage($this->inputImage["image"]);

        $imageA->rotate(270);
        $imageB->rotateRight();

        ob_start();
        imagepng($imageA->Resource);
        $pngA = ob_get_contents();
        ob_end_clean();

        ob_start();
        imagepng($imageB->Resource);
        $pngB = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($pngA, $pngB);
    }
    #endregion

    #region Cropping
    /**
     * @covers Image::crop
     */
    public function testCanBeCropped() {
        $image = Image::fromImage($this->inputImage["image"]);

        $rectangle = array(
            "x" => 1,
            "y" => 2,
            "width" => 3,
            "height" => 4
        );

        $image->crop($rectangle);

        $image = Image::fromResource($image->Resource);

        $this->assertEquals($rectangle["width"], $image->Width);
        $this->assertEquals($rectangle["height"], $image->Height);
    }
    #endregion

    #region Joining
    public function testCanJoinSmallerImage() {
        $biggerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage->resizeW($smallerImage->Width - 20);

        $biggerImage->join($smallerImage);

        $this->checkImage($biggerImage);
    }

    public function testCanNotJoinBiggerImage() {
        $biggerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage->resizeW($smallerImage->Width - 20);

        // Try case: Bigger image
        try {
            $smallerImage->join($biggerImage);
            $this->fail("{$biggerImage} successfully inserted into {$smallerImage}.");
        }
        catch(ImageException $ie) {
            $this->assertEquals(ImageException::OutOfRangeException, $ie->getCode());
        }
    }

    public function testCanJoinImageAtSpecificPosition() {
        $biggerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage = Image::fromImage($this->inputImage["image"]);
        $smallerImage->resizeW($smallerImage->Width - 20);

        foreach($this->supportedPositions as $position) {
            $biggerImage->join($smallerImage, $position);
            $this->checkImage($biggerImage);
        }
    }
    #endregion

    #region Output
    /**
     * @throws ImageException
     */
    public function testCanSaveImage() {
        $image = Image::fromImage($this->inputImage["image"]);
        $pathToTemporaryImage = $image->PathToFile.".tmp";
        foreach($this->supportedFormats as $format) {
            $image->save($pathToTemporaryImage, $format);
            $temporaryImage = Image::fromFile($pathToTemporaryImage);
            $this->checkImage($temporaryImage);
            unset($temporaryImage);
        }
        @unlink($pathToTemporaryImage);

        // Try case: Unsupported format
        try {
            $image->save($pathToTemporaryImage, "UnsupportedFormat");
            $this->fail("Unsupported format doesn't throw exception.");
        }
        catch(ImageException $ie) {
            $this->assertEquals(ImageException::UnsupportedFormatException, $ie->getCode());
            $this->assertFalse(file_exists($pathToTemporaryImage));
        }
    }
    #endregion
}