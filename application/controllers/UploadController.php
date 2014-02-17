<?php

class UploadController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $response = new stdClass();

        try {
            $upload = new Zend_File_Transfer();
        } catch (Exception $e) {
            $response->error = $e->getMessage();
            $this->_helper->json->sendJson($response);
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

            $hashDoc = new Unsee_Mongo_Document_Hash();
            $response->hash = $hashDoc->hash;

            foreach ($files as $file => &$info) {
                if (!$upload->isUploaded($file)) {
                    $info = null;
                } else {
                    $image = new Unsee_Mongo_Document_Image();
                    $image->readFile($info['tmp_name']);
                    $hashDoc->addImage($image);
                }
            }

            $hashDoc->save();
        }

        $this->_helper->json->sendJson($response);
    }
}
