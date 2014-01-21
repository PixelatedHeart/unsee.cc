<?php

class ViewController extends Zend_Controller_Action
{

    public function init()
    {
        $assetsDomain = Zend_Registry::get('config')->assetsDomain;
        $this->view->headScript()->appendFile($assetsDomain . '/js/view.js');

        $request = new Zend_Controller_Request_Http();
        $dnt = $request->getHeader('DNT');

        if (!$dnt) {
            $this->view->headScript()->appendFile($assetsDomain . '/js/track.js');
        }

        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/normalize.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/h5bp.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/view.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/main.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/subpage.css');
    }

    public function indexAction()
    {
        $params = $this->getAllParams();

        if (empty($params['hash'])) {
            return $this->deletedAction();
        }

        // Hash (ababab)
        $hashString = $params['hash'];

        // Get hash document
        $hashDoc = Unsee_Mongo_Document_Hash::one(array('hash' => $hashString));

        // It was already deleted or did not exist
        if (!$hashDoc) {
            return $this->deletedAction();
        }

        // Get time to die
        $ttd = $hashDoc->timestamp->sec + $hashDoc->ttl;

        // Single-view image was viewed or ttl image was outdated
        if (!$hashDoc->ttl && $hashDoc->views || $hashDoc->ttl && time() >= $ttd) {
            $hashDoc->delete();
            return $this->deletedAction();
        }

        // If viewer is the creator - don't count their view
        if (!$hashDoc->isOwner()) {
            $hashDoc->views++;
            $hashDoc->save();
        }

        $deleteMessage = !$hashDoc->ttl ? 'delete_first' : 'delete_time';

        // Don't show 'other party' text to the 'other party'
        if ($hashDoc->isOwner() || $hashDoc->ttl) {
            $this->view->deleteTime = $this->view->translate($deleteMessage, array(date("c", $ttd)));
        }

        $imagesList = $hashDoc->getImagesIds();

        $this->view->images = array();
        foreach ($imagesList as $key => $imageId) {
            $ticketTtd = $_SERVER['REQUEST_TIME'] + $key + 1; // Each image would be loaded a second later
            // Preparing a hash for nginx's secure link
            $md5 = base64_encode(md5($imageId . $ticketTtd, true));
            $md5 = strtr($md5, '+/', '-_');
            $md5 = str_replace('=', '', $md5);

            $this->view->images[$imageId] = array('hash' => $md5, 'ticketTtd' => $ticketTtd);
        }
    }

    public function deletedAction()
    {
        $this->render('deleted');
        return $this->getResponse()->setHttpResponseCode(310);
    }

    public function imageAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $imageId = $this->getParam('id');
        $ticket = $this->getParam('ticket');
        $time = $this->getParam('time');

        if (!$imageId || !$ticket || !$time || $time < time()) {
            die();
        }

        $imgDoc = Unsee_Mongo_Document_Image::one(array('_id' => new MongoId($imageId)));
        $imgDoc->watermark();

        header('Content-type: ' . $imgDoc->type);
        header('Content-length: ' . strlen($imgDoc->data));
        print $imgDoc->data;
    }

    protected function getImageData($imgId)
    {
        $img = Unsee_Mongo_Document_Image::one(array('_id' => new MongoId($imgId)), array('_id'));
    }
}
