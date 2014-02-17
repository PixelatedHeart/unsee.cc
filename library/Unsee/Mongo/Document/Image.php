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

    public function readFile($filePath)
    {
        $image = new Imagick();
        $image->readimage($filePath);
        $image->stripimage();

        $info = getimagesize($filePath);

        $this->data = new MongoBinData($image, MongoBinData::BYTE_ARRAY);
        $this->size = filesize($filePath);
        $this->type = $info['mime'];
        $this->width = $info[0];
        $this->height = $info[1];
    }

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
        $this->data = new MongoBinData($image->getimageblob(), MongoBinData::BYTE_ARRAY);

        return true;
    }

    public function watermark()
    {
        $text = $_SERVER['REMOTE_ADDR'];
        $image = imagecreatefromstring($this->data->bin);
        $font = $_SERVER['DOCUMENT_ROOT'] . '/pixel.ttf';
        $im = imagecreatetruecolor(800, 800);

        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagettftext($im, 12, 0, 100, 100, -imagecolorallocatealpha($im, 255, 255, 255, 80), $font, $text);
        imagealphablending($im, true);
        imagesettile($image, $im);
        imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), IMG_COLOR_TILED);

        ob_start();
        imagejpeg($image);

        $this->data = new MongoBinData(ob_get_clean(), MongoBinData::BYTE_ARRAY);
        $this->size = strlen($this->data);

        return true;
    }

    public function comment($comment)
    {
        $dict = array(
            '%ip%'         => $_SERVER['REMOTE_ADDR'],
            '%user_agent%' => $_SERVER['HTTP_USER_AGENT']
        );

        $comment = str_replace(array_keys($dict), $dict, $comment);

        $image = new Imagick();
        $image->readimageblob($this->data->bin);
        $image->commentimage($comment);
        $this->data = new MongoBinData($image->getimageblob(), MongoBinData::BYTE_ARRAY);
        return true;
    }
}
