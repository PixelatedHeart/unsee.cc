<?php

class Unsee_Ticket extends Unsee_Redis
{

    protected $db = 2;
    static public $ttl = 30;

    public function __construct()
    {
        parent::__construct(Unsee_Session::getCurrent());
    }

    public function issue($imageId)
    {
        $this->$imageId = time();
        return true;
    }

    public function isAllowed($imageDoc)
    {
        return isset($this->{$imageDoc->key}) && isset($_COOKIE[md5(Unsee_Session::getCurrent() . $imageDoc->hash)]);
    }

    public function invalidate($imageDoc)
    {
        unset($this->{$imageDoc->key});
        return true;
    }
}
