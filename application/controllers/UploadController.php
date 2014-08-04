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
        $upload->addValidator('Size', false, array('max' => '10MB', 'bytestring' => false));
        $translate = Zend_Registry::get('Zend_Translate');
        $updating = false;

        try {
            if (!$upload->receive()) {
                throw new Exception($translate->translate('error_uploading'));
            } else {
                $files = $upload->getFileInfo();


                // Updating hash with new images
                if (!empty($_POST['hash']) && Unsee_Hash::isValid($_POST['hash'])) {
                    $hashDoc = new Unsee_Hash($_POST['hash']);
                    $updating = true;
                    $response = array();

                    if (!Unsee_Session::isOwner($hashDoc) && !$hashDoc->allow_anonymous_images) {
                        die('[]');
                    }
                } else {
                    // Creating a new hash
                    $hashDoc = new Unsee_Hash();
                    $this->setExpiration($hashDoc);
                    $response->hash = $hashDoc->key;
                }

                $imageAdded = false;

                foreach ($files as $file => $info) {
                    if ($upload->isUploaded($file)) {
                        $imgDoc = new Unsee_Image($hashDoc);
                        $res = $imgDoc->setFile($info['tmp_name']);
                        $imgDoc->setSecureParams(); //hack to populate correct secureTtd

                        if ($updating) {
                            $ticket = new Unsee_Ticket();
                            $ticket->issue($imgDoc);

                            $newImg = new stdClass();
                            $newImg->hashKey = $hashDoc->key;
                            $newImg->key = $imgDoc->key;
                            $newImg->src = '/image/' . $imgDoc->key . '/' . $imgDoc->secureMd5 . '/' . $imgDoc->secureTtd . '/';
                            $newImg->width = $imgDoc->width;
                            $newImg->ticket = md5(Unsee_Session::getCurrent() . $hashDoc->key);

                            $response[] = $newImg;
                        }

                        if ($res) {
                            $imageAdded = true;
                        }

                        // Remove uploaded file from temporary dir if it wasn't removed
                        if (file_exists($info['tmp_name'])) {
                            @unlink($info['tmp_name']);
                        }
                    }
                }

                if (!$imageAdded) {
                    throw new Exception('No images were added');
                }
            }
        } catch (Exception $e) {
            $response->error = $e->getMessage();
        }
        $this->_helper->json->sendJson($response);
    }

    /**
     * Sets the TTL for the provided hash
     * @param Unsee_Hash $hashDoc
     * @return boolean
     */
    private function setExpiration($hashDoc)
    {
        // Custom ttl was set
        if (!empty($_POST['time']) && in_array($_POST['time'], Unsee_Hash::$ttlTypes)) {
            $amount = array_search($_POST['time'], Unsee_Hash::$ttlTypes);
            if ($amount > 0) {
                // Disable single view, which is ON by default
                $hashDoc->max_views = 0;
                $hashDoc->ttl = $_POST['time'];
                // Expire in specified interval, instead of a day
                $hashDoc->expireAt(time() + $amount);
            }
        }

        return true;
    }
}
