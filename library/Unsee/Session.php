<?php

class Unsee_Session
{

    static public function getCurrent()
    {
        return md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    static public function isOwner($hashDoc)
    {
        return self::getCurrent() === $hashDoc->sess;
    }

    static public function getImageSession($imageDoc)
    {
        return md5(Unsee_Session::getCurrent() . $imageDoc->secureMd5);
    }
}
