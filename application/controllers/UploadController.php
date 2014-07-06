<?php

/**
 * Upload controller
 * @todo Use Zend_Form instead of a plain html in view
 */
class UploadController extends Zend_Controller_Action
{

    /**
     * Controller to handle file upload form
     * @throws Exception
     */
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
        $upload->addValidator('Size', false, array('max' => '8MB', 'bytestring' => false));
        $translate = Zend_Registry::get('Zend_Translate');

        try {
            if (!$upload->receive()) {
                throw new Exception();
            } else {
                $files = $upload->getFileInfo();

                // Tell the page the name of the new hash
                $response->hash = $this->getNewHashName();

                foreach ($files as $file => &$info) {
                    if (!$upload->isUploaded($file)) {
                        $info = null;
                    } else {
                        $imgDoc = new Unsee_Image($response->hash . '_'.  uniqid());
                        $imgDoc->setFile($info['tmp_name']);
                    }
                }
            }
        } catch (Exception $e) {
            $response->error = $translate->translate('error_uploading');
        }
        $this->_helper->json->sendJson($response);
    }

    /**
     * Creates a new hash document and returns it's name
     * @return type
     */
    private function getNewHashName()
    {
        // Creating a new hash item (/bababa/)
        $hashDoc = new Unsee_Hash();

        if (isset($_POST['time']) && in_array($_POST['time'], Unsee_Hash::$_ttlTypes)) {
            $hashDoc->ttl = $_POST['time'];
        }

        return $hashDoc->key;
    }
}
