<?php

/**
 * Helper class to fetch session data
 */
class Unsee_Session
{

    /**
     * Returns current session hash
     * @return string
     */
    static public function getCurrent()
    {
        return md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Returns true if the provided image model belongs to current viewer
     * @param Unsee_Hash $hashDoc
     * @return true
     */
    static public function isOwner($hashDoc)
    {
        return self::getCurrent() === $hashDoc->sess;
    }
}
