<?php

class Unsee_Mongo_Document_Hash extends Shanty_Mongo_Document
{

    protected static $_db = 'unsee';
    protected static $_collection = 'hashes';
    protected static $_requirements = array(
        'hash'      => array('Required'),
        'sess'      => array('Required'),
        'timestamp' => array('Required', 'Validator:MongoDate')
    );

    public function getImagesIds()
    {
        $images = Unsee_Mongo_Document_Image::all(array('hashId' => $this->getId()), array('_id'));

        if (!$images) {
            return array();
        }

        $imagesArray = array();

        foreach ($images as $item) {
            $imagesArray[] = (string) $item->getId();
        }

        return $imagesArray;
    }

    private function getCurrentSession()
    {
        return md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    public function isOwner()
    {
        return $this->getCurrentSession() === $this->getProperty('sess');
    }

    protected function preInsert()
    {
        $this->_data['sess'] = $this->getCurrentSession();
    }

    protected function postDelete()
    {
        
    }
}
