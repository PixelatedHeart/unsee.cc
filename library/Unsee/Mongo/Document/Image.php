<?php

class Unsee_Mongo_Document_Image extends Shanty_Mongo_Document
{

    protected static $_db = 'unsee';
    protected static $_collection = 'images';
    protected static $_requirements = array(
        'size'   => array('Required'),
        'type'   => array('Required'),
        'data'   => array('Required'),
        'hashId' => array('Required', 'Validator:MongoId')
    );
    protected $iMagick;

    protected function getImaick()
    {
        if (!$this->iMagick) {
            $iMagick = new Imagick();
            $iMagick->readimageblob($this->data->bin);
            $this->iMagick = $iMagick;
        }

        return $this->iMagick;
    }

    public function stripExif()
    {
        $image = $this->getImaick();
        $image->stripImage();
        $this->data = new MongoBinData($image->getimageblob());

        return true;
    }

    public function watermark()
    {
        // Create objects
        $image = $this->getImaick();

        // Watermark text
        $watermark = new Imagick();
        $text = $_SERVER['REMOTE_ADDR'];

        // Create a new drawing palette
        $draw = new ImagickDraw();
        $watermark->newImage(240, 80, new ImagickPixel('none'));

        // Set font properties
        $draw->setFont('Courier');
        $draw->setFillColor('White');
        $draw->setfontsize(30);
        $draw->setFillOpacity(.4);

        // Position text at the top left of the watermark
        $draw->setGravity(Imagick::GRAVITY_NORTHWEST);

        // Draw text on the watermark
        $watermark->annotateImage($draw, 10, 10, 0, $text);

        // Position text at the bottom right of the watermark
        $draw->setGravity(Imagick::GRAVITY_SOUTHEAST);

        // Repeatedly overlay watermark on image
        for ($w = 0; $w < $image->getImageWidth(); $w += 600) {
            for ($h = 0; $h < $image->getImageHeight(); $h += 600) {
                $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $w, $h);
            }
        }

        list(, $format) = explode('/', $this->type);

        // Set output image format
        $image->setImageFormat($format);

        /*
        $comment = 'The image was not intended for sharing, ' .
                'but was foully taken from https://www.unsee.cc/ ' .
                'on ' . date('c') . '.' . PHP_EOL .
                'Below is info on the bad person:' . PHP_EOL .
                'IP: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL .
                'User agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;

        $image->commentimage($comment);
         */
        $this->data = new MongoBinData($image->getimageblob());
        $this->size = $image->getimagesize();

        return true;
    }
}
