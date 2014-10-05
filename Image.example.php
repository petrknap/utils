<?php

require_once("./Image.class.php");

use PetrKnap\Utils\ImageProcessing\Image;

$img = Image::fromFile("./Image.example.jpg");
$img->resizeW(480);

$tmpImg = Image::fromImage($img);
$tmpImg->resize(120, 80);
$tmpImg->rotateLeft();
$img->join($tmpImg, Image::RightTop);
$tmpImg->rotateRight();
$img->join($tmpImg, Image::CenterCenter);
$tmpImg->rotate(180);
$img->join($tmpImg, Image::LeftBottom);
unset($tmpImg);

$watermark = Image::fromFile("./Image.example.png");
$img->join($watermark);
unset($watermark);

$img->rotate(30);
$img->show();
$img->save('./Image.example.gif', Image::GIF);
unset($img);