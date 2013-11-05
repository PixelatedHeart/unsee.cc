<?php

class UploadController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $response = new stdClass();
        $upload = new Zend_File_Transfer();

        $upload->addValidator('Count', false, array('min' => 1, 'max' => 100));
        $upload->addValidator('IsImage', false);

        if (!$upload->receive()) {
            $translate = Zend_Registry::get('Zend_Translate');
            $response->error = $translate->translate('error_uploading');
        } else {
            $files = $upload->getFileInfo();

            $newHash = (string) new Unsee_Hash();

            foreach ($files as $file => &$info) {
                if (!$upload->isUploaded($file)) {
                    $info = null;
                }
            }

            $hashDoc = new Unsee_Mongo_Document_Hash();
            $hashDoc->hash = $newHash;
            $hashDoc->timestamp = time();
            $hashDoc->ttl = 0;
            $hashDoc->views = 0;
            $hashDoc->save();

            $response->hash = $newHash;
        }

        $this->_helper->json->sendJson($response);
    }
}