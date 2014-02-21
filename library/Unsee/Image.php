<?php

class Unsee_Image extends Unsee_Redis
{

    public $data;
    protected $db = 1;
    protected $iMagick;

    public function delete()
    {
        unlink($this->getFilePath());
        parent::delete();
    }

    public function __construct($key = null)
    {

        if (empty($key)) {
            $key = uniqid();
        }

        parent::__construct($key, 1);
    }

    public function getSecureLink($ttlSeconds)
    {
        // Keep alive as long as possible in case image would not be viewed at once
        if (!$ttlSeconds) {
            end(Unsee_Hash::$_ttlTypes);
            $ticketTtd = $ttlSeconds + key(Unsee_Hash::$_ttlTypes);
            reset(Unsee_Hash::$_ttlTypes);
        } else {
            $ticketTtd = time() + $ttlSeconds;
        }

        // Preparing a hash for nginx's secure link
        $md5 = base64_encode(md5($this->key . $ticketTtd, true));
        $md5 = strtr($md5, '+/', '-_');
        $md5 = str_replace('=', '', $md5);

        return $md5 . '/' . $ticketTtd . '/';
    }

    public function setFile($filePath)
    {
        $image = new Imagick();
        $image->readimage($filePath);
        $image->stripimage();

        $filePath = $this->getFilePath();
        $filePathDir = dirname($filePath);

        if (!is_dir($filePathDir)) {
            mkdir($filePathDir, 0755);
        }

        file_put_contents($filePath, $image->getimageblob());

        $info = getimagesize($filePath);

        $this->size = filesize($filePath);
        $this->type = $info['mime'];
        $this->width = $info[0];
        $this->height = $info[1];

        return true;
    }

    protected function getFilePath()
    {
        $storage = Zend_Registry::get('config')->storagePath;
        $file = $storage . $this->hash . '/' . $this->key;
        return $file;
    }

    public function getImageData()
    {
        if (empty($this->data)) {
            $this->data = file_get_contents($this->getFilePath());
        }

        return $this->data;
    }

    protected function getImagick()
    {
        if (!$this->iMagick) {
            $iMagick = new Imagick();
            $iMagick->readimageblob($this->getImageData());
            $this->iMagick = $iMagick;
        }

        return $this->iMagick;
    }

    public function stripExif()
    {
        $this->getImagick()->stripImage();
        return true;
    }

    public function watermark()
    {
        $text = $_SERVER['REMOTE_ADDR'];
        $image = imagecreatefromstring($this->getImageData());
        $font = $_SERVER['DOCUMENT_ROOT'] . '/pixel.ttf';
        $im = imagecreatetruecolor(800, 800);

        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagettftext($im, 12, 0, 100, 100, -imagecolorallocatealpha($im, 150, 150, 150, 70), $font, $text);
        imagealphablending($im, true);
        imagesettile($image, $im);
        imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), IMG_COLOR_TILED);

        $func = str_replace('/', '', $this->type);
        if (strpos($func, 'image') !== 0 || !function_exists($func)) {
            $func = 'imagejpeg';
        }

        ob_start();
        // TODO: imagick should support all formats
        /* $func */imagejpeg($image, null, 85);

        $this->data = ob_get_clean();
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
        $this->getImagick()->commentimage($comment);
        $this->data = $this->getImagick()->getImageBlob();
        return true;
    }
}
