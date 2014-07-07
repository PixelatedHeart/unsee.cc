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

    const DB = 1;

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

    public function __construct($hash)
    {
        parent::__construct($hash);
        $this->setSecureParams();
    }

    /**
     * Sets the params needed for the secure link nginx module to work
     * @see http://wiki.nginx.org/HttpSecureLinkModule
     * @return boolean
     */
    public function setSecureParams()
    {
        
        $linkTtl = Unsee_Ticket::$ttl;
        
        if (!$this->no_download) {
            $linkTtl = $this->ttl();
        }

        $this->secureTtd = time() + $linkTtl;

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
        $info = getimagesize($filePath);

        $image = new Imagick();
        $image->readimage($filePath);
        $image->stripimage();

        $this->size = filesize($filePath);
        $this->type = $info['mime'];
        $this->width = $info[0];
        $this->height = $info[1];
        $this->content = $image->getImageBlob();
        $this->expireAt(time() + static::EXP_DAY);

        return true;
    }

    /**
     * Instantiates and returns Image Magick object
     * @return \imagick
     */
    protected function getImagick()
    {
        if (!$this->iMagick) {
            $iMagick = new Imagick();
            $iMagick->readimageblob($this->content);
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
        list($realHash) = explode('_', $this->key);

        $hashDoc = new Unsee_Hash($realHash);

        if (Unsee_Session::isOwner($hashDoc)) {
            return true;
        }

        $text = $_SERVER['REMOTE_ADDR'];
        $image = imagecreatefromstring($this->content);
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

        $this->content = ob_get_clean();
        $this->size = strlen($this->content);

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
        $this->content = $this->getImagick()->getImageBlob();
        return true;
    }
}
