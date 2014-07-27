<?php

/**
 * Hash image model
 */
class Unsee_Image extends Unsee_Redis
{

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

    public function __construct(Unsee_Hash $hash, $imgKey = null)
    {
        $newImage = is_null($imgKey);

        if ($newImage) {
            $imgKey = uniqid();
        }

        parent::__construct($hash->key . '_' . $imgKey);

        if ($newImage) {
            $keys = Unsee_Image::keys($hash->key . '*');
            $this->num = count($keys);
            $this->expireAt(time() + $hash->ttl());
        }

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
            $linkTtl = $this->ttl(true);
        }

        $this->secureTtd = round(microtime(true) + $linkTtl);

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
        if (!file_exists($filePath)) {
            return false;
        }

        $info = getimagesize($filePath);
        $imageWidth = $info[0];
        $imageHeight = $info[1];

        $image = new Imagick();
        $image->readimage($filePath);

        $image->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 1);
        $maxSize = 1920;

        if ($imageWidth > $maxSize && $imageWidth > $imageHeight) {
            $image->thumbnailimage($maxSize, null);
        } elseif ($imageHeight > $maxSize && $imageHeight > $imageWidth) {
            $image->thumbnailimage(null, $maxSize);
        }

        $image->setCompression(Imagick::COMPRESSION_JPEG);
        $image->setCompressionQuality(80);

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
        $text = $_SERVER['REMOTE_ADDR'];
        $font = $_SERVER['DOCUMENT_ROOT'] . '/pixel.ttf';
        $image = $this->getImagick();

        $width = $image->getimagewidth();

        $watermark = new Imagick();
        $watermark->newImage(1000, 1000, new ImagickPixel('none'));

        $draw = new ImagickDraw();
        $draw->setFont($font);
        $draw->setfontsize(30);
        $draw->setFillColor('gray');
        $draw->setFillOpacity(.3);
        $watermark->annotateimage($draw, 100, 200, -45, $text);
        $watermark->annotateimage($draw, 550, 550, 45, $text);

        $this->iMagick = $image->textureimage($watermark);

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

        return true;
    }

    /**
     * Returns image binary content
     * @return type
     */
    public function getImageContent()
    {
        if ($this->iMagick) {
            return $this->iMagick->getimageblob();
        } else {
            return $this->content;
        }
    }
}
