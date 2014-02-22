<?php

class Unsee_Hash extends Unsee_Redis
{

    public static $_ttlTypes = array(-1 => 'now', 0 => 'first', 3600 => 'hour', 86400 => 'day', 604800 => 'week');

    public function __construct($key = null)
    {

        parent::__construct($key);

        if (empty($key)) {
            $this->setNewHash();
            $this->timestamp = time();
            $this->ttl = self::$_ttlTypes[0];
            $this->views = 0;
            $this->no_download = true;
            $this->strip_exif = true;
            $this->comment = Zend_Registry::get('config')->image_comment;
            $this->sess = $this->getCurrentSession();
        }
    }

    protected function setNewHash()
    {
        $hashConf = Zend_Registry::get('config')->hash->toArray();

        $vovels = str_split($hashConf['vovels']);
        $consonants = str_split($hashConf['consonants']);
        $syllableNum = (int) $hashConf['syllables'];

        shuffle($vovels);
        shuffle($consonants);

        $hash = '';

        for ($x = 1; $x <= $syllableNum; $x++) {
            $hash .= array_pop($consonants) . array_pop($vovels);
        }

        $this->key = $hash;

        return $this->exists() && $this->setNewHash();
    }

    public function getImages()
    {
        // read files in directory
        $storage = Zend_Registry::get('config')->storagePath;
        $files = glob($storage . $this->key . '/*');
        $imageDocs = array();
        foreach ($files as $file) {
            $imageDocs[] = new Unsee_Image(basename($file));
        }

        return $imageDocs;
    }

    public function delete()
    {
        // Delete images
        $images = $this->getImages();

        foreach ($images as $item) {
            $item->delete();
        }

        // Remove hash storage sub-dir
        $dir = Zend_Registry::get('config')->storagePath . '/' . $this->key;
        if (is_dir($dir)) {
            rmdir($dir);
        }

        parent::delete();
    }

    private function getCurrentSession()
    {
        return md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    public function isOwner()
    {
        return $this->getCurrentSession() === $this->sess;
    }

    public function isViewable()
    {
        if ($this->ttl === 'first' && !$this->views) {
            // Single-view image hasn't been viewed yet
            return true;
        } elseif ($this->ttl !== 'first' && $this->getTtlSeconds() > 0) {
            // Image not yet outdated
            return true;
        } else {
            // Dead
            return false;
        }
    }

    public function getTtlSeconds()
    {
        // Converting ttl into strtotime acceptable string
        switch ($this->ttl) {
            // Date in past for right now
            case 'now':
                $ttl = '-1 day';
                break;
            // Delete on first view, use one second
            case 'first':
                return 1;
            // almost strtotime-ready otherwise (time value)
            default:
                $ttl = '+1 ' . $this->ttl;
                break;
        }

        // Get time to die
        return strtotime($ttl, $this->timestamp) - time();
    }

    public function getTtlWords()
    {
        $secondsLeft = $this->getTtlSeconds();
        $lang = Zend_Registry::get('Zend_Translate');

        if ($secondsLeft < 60) {
            return $lang->translate('moment');
        }

        $times = array();
        $timeStrings = array();
        $foundNonEmpty = false;

        $times['day'] = strtotime('+1 day', 0);
        $times['hour'] = $times['day'] / 24;
        $times['minute'] = $times['hour'] / 60;

        foreach ($times as $timeFrame => &$seconds) {
            // Days/hours/minutes left
            $itemsLeft = floor($secondsLeft / $seconds);

            // Recalculate number of seconds left - minus seconds in current day/hour/minute
            $secondsLeft -= $itemsLeft * $seconds;
            // Trying to translate the number correctly
            $modRes = $itemsLeft % 10;
            if ($modRes === 1) {
                $timeFrame .= '_one';
            } elseif ($modRes > 1 && $modRes < 5) {
                $timeFrame .= '_couple';
            } else {
                $timeFrame .= '_many';
            }

            if ($itemsLeft || $foundNonEmpty) {
                $foundNonEmpty = true;
                $timeStrings[] = $itemsLeft . ' ' . $lang->translate($timeFrame);
            }
        }

        // Use last element anyway
        $deleteTime = array_pop($timeStrings);

        // If it's not the only one - prepend others
        if ($timeStrings) {
            $deleteTime = implode(', ', $timeStrings) . ' ' . $lang->translate('and') . ' ' . $deleteTime;
        }

        return $deleteTime;
    }
}
