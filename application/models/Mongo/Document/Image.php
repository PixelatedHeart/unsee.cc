<?php

class Unsee_Mongo_Document_Image extends Shanty_Mongo_Document
{

    protected static $_db = 'unsee';
    protected static $_collection = 'images';
    protected static $_requirements = array(
        'size'   => array('Required'),
        'type'   => array('Required'),
        'data'   => array('Required'),
        'hashId' => array('Required', 'Validator:MongoId')
    );

}
