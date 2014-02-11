<?php

class UploadController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $response = new stdClass();
        $upload = new Zend_File_Transfer();

        $ttlTypes = Unsee_Mongo_Document_Hash::$_ttlTypes;
        $ttl = $this->getParam('time', 0); // 0 means now

        if (!in_array($ttl, $ttlTypes)) {
            $ttl = $ttlTypes[0];
        }

        $upload->addValidator('Count', false, array('min' => 1, 'max' => 100));
        $upload->addValidator('IsImage', false);
        //Limit individual file size to 4M, since BSON MongoDB object is capped to that amount
        $upload->addValidator('Size', false, array('max' => '4MB', 'bytestring' => false));
        $translate = Zend_Registry::get('Zend_Translate');

        if (!$upload->receive()) {
            $response->error = $translate->translate('error_uploading');
        } else {
            $files = $upload->getFileInfo();

            $newHash = (string) new Unsee_Hash();

            if (!$newHash) {
                $response->error = $translate->translate('error_uploading');
            }

            $hashDoc = new Unsee_Mongo_Document_Hash();
            $hashDoc->hash = $newHash;
            $hashDoc->timestamp = new MongoDate();
            $hashDoc->ttl = $ttl;
            $hashDoc->views = 0;
            $hashDoc->strip_exif = '1';
            $hashDoc->comment = "Image was taken from https://unsee.cc/$newHash/ by %ip% (%user_agent%)";
            $hashDoc->save();

            foreach ($files as $file => &$info) {
                if (!$upload->isUploaded($file)) {
                    $info = null;
                } else {

                    $imageContent = file_get_contents($info['tmp_name']);
                    $image = new Imagick();
                    $image->readimageblob($imageContent);
                    $image->stripimage();
                    $imageDoc = new Unsee_Mongo_Document_Image();
                    $imageDoc->hashId = $hashDoc->getId();
                    $imageDoc->data = new MongoBinData($image);
                    $imageDoc->size = $info['size'];
                    $imageDoc->type = $info['type'];

                    $size = getimagesize($info['tmp_name']);
                    
                    $imageDoc->width = $size[0];
                    $imageDoc->height = $size[1];

                    $imageDoc->save();
                }
            }

            $response->hash = $newHash;
        }

        $this->_helper->json->sendJson($response);
    }
}
