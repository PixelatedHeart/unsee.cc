<?php

class Unsee_Hash
{

    private $hash = '';
    protected $vovels = '';
    protected $consonants = '';
    protected $syllableNum = 3;

    public function __construct()
    {
        $hashConf = Zend_Registry::get('config')->hash->toArray();

        $this->vovels = $hashConf['vovels'];
        $this->consonants = $hashConf['consonants'];
        $this->syllableNum = $hashConf['syllables'];
    }

    public function __toString()
    {
        if (empty($this->hash)) {
            $this->generate();
        }

        return $this->hash;
    }

    public function generate()
    {
        $vovels = str_split($this->vovels);
        $consonants = str_split($this->consonants);

        shuffle($vovels);
        shuffle($consonants);

        $hash = '';

        for ($x = 1; $x <= $this->syllableNum; $x++) {
            $hash .= array_pop($consonants) . array_pop($vovels);
        }

        $exists = $this->hashExists($hash);

        if (is_null($exists)) {
            return false;
        }

        if ($exists) {
            return $this->generate();
        }

        $this->hash = $hash;

        return $hash;
    }

    protected function hashExists($hash)
    {
        try {
            return (bool) Unsee_Mongo_Document_Hash::one(array('hash' => $hash));
        } catch (Exception $e) {
            // Mongo is having problems
            return null;
        }
    }

    public function validate($hash)
    {
        return preg_match('~^((?:[' . $this->consonants . '][' . $this->vovels . ']){' . $this->syllableNum . '})$~i',
                          $hash);
    }
}
