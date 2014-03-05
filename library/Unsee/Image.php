<?php

/**
 * Hash image model
 */
class Unsee_Image extends Unsee_Redis
{

    /**
     * Image content
     * @var string
     */
    public $data;

    /**
     * Database id
     * @var int
     */
    protected $db = 1;

    /**
     * Image Magick instance
     * @var \imagick
     */
    protected $iMagick;

    /**
     * Secure link md5
     */
    public $secureMd5 = '';

    /**
     * Secure link unix time
     * @var type 
     */
    public $secureTtd = 0;

    /**
     * Deletes the image model and the file associated with it
     */
    public function delete()
    {
        unlink($this->getFilePath());
        $dir = Zend_Registry::get('config')->storagePath . '/' . $this->hash;
        !glob($dir . '/*') && rmdir($dir);
        parent::delete();
    }

    public function __construct($key = null)
    {

        if (empty($key)) {
            $key = uniqid();
        }

        parent::__construct($key, 1);
        $this->setSecureParams();
    }

    /**
     * Sets the params needed for the secure link nginx module to work
     * @see http://wiki.nginx.org/HttpSecureLinkModule
     * @return boolean
     */
    public function setSecureParams()
    {
        $this->secureTtd = time() + Unsee_Ticket::$ttl;

        // Preparing a hash for nginx's secure link
        $md5 = base64_encode(md5($this->key . $this->secureTtd, true));
        $md5 = strtr($md5, '+/', '-_');
        $md5 = str_replace('=', '', $md5);

        $this->secureMd5 = $md5;
        return true;
    }

    /**
     * Associates the model with the file
     * @param string $filePath
     * @return boolean
     */
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

    /**
     * Returns the file path of for the model's image
     * @return string
     */
    protected function getFilePath()
    {
        $storage = Zend_Registry::get('config')->storagePath;
        $file = $storage . $this->hash . '/' . $this->key;
        return $file;
    }

    /**
     * Sets and returns the content of the image file
     * @return string
     */
    public function getImageData()
    {
        if (empty($this->data)) {
            $this->data = file_get_contents($this->getFilePath());
        }

        return $this->data;
    }

    /**
     * Instantiates and returns Image Magick object
     * @return \imagick
     */
    protected function getImagick()
    {
        if (!$this->iMagick) {
            $iMagick = new Imagick();
            $iMagick->readimageblob($this->getImageData());
            $this->iMagick = $iMagick;
        }

        return $this->iMagick;
    }

    /**
     * Strips exif data from image body
     * @return boolean
     */
    public function stripExif()
    {
        $this->getImagick()->stripImage();
        return true;
    }

    /**
     * Watermars the image with the viewer's IP
     * @return boolean
     */
    public function watermark()
    {
        if (Unsee_Session::isOwner(new Unsee_Hash($this->hash))) {
            return true;
        }

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

    /**
     * Embeds a comment into the image body
     * @param string $comment
     * @return boolean
     */
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
