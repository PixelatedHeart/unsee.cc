<?php

class ViewController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile('js/view.js');

        $request = new Zend_Controller_Request_Http();
        if (!$request->getHeader('DNT')) {
            $this->view->headScript()->appendFile('js/track.js');
        }

        $this->view->headLink()->appendStylesheet('css/normalize.css');
        $this->view->headLink()->appendStylesheet('css/h5bp.css');
        $this->view->headLink()->appendStylesheet('css/view.css');
        $this->view->headLink()->appendStylesheet('css/subpage.css');
    }

    private function handleSettingsFormSubmit($form, $hashDoc)
    {
        $form = new Application_Form_Settings();

        if ($form->isValid($_POST)) {
            $values = $form->getValues();

            foreach ($values as $field => $value) {
                $hashDoc->$field = $value;
            }
            $hashDoc->save();
        }
    }

    public function indexAction()
    {
        $params = $this->getAllParams();
        $form = new Application_Form_Settings;
        // Hash (ababab)
        $hashString = $params['hash'];

        // Get hash document
        $hashDoc = Unsee_Mongo_Document_Hash::one(array('hash' => $hashString));

        if ($this->getRequest()->isPost()) {
            $this->handleSettingsFormSubmit($form, $hashDoc);
        }

        if (empty($params['hash'])) {
            return $this->deletedAction();
        }

        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex');

        // Or custon hash = 
        // It was already deleted or did not exist
        if (!$hashDoc) {
            return $this->deletedAction();
        }

        // Handle current request based on what settins are set
        $props = $hashDoc->getPropertyKeys();

        foreach ($props as $item) {
            $item = explode('_', $item);

            foreach ($item as &$itemItem) {
                $itemItem = ucfirst($itemItem);
            }

            $method = 'handle' . implode('', $item);

            if (method_exists($this, $method)) {
                $this->$method($hashDoc);
            }
        }

        $ttl = $hashDoc->ttl;

        // Converting ttl into strtotime acceptable string

        // Delete now, expire
        if ($ttl === 'now') {
            $ttl = '-1 day';
        } elseif ($ttl === 'first') { // Delete on first view, use zero
            $ttl = 0;
        } else { // almost strtotime-ready otherwise (time value)
            $ttl = '+1 ' . $ttl;
        }

        // Get time to die
        $ttd = strtotime($ttl, $hashDoc->timestamp->sec);

        // Single-view image was viewed or ttl image was outdated
        if (!$ttl && $hashDoc->views || $ttl && time() >= $ttd) {
            $hashDoc->delete();
            return $this->deletedAction();
        }

        $this->view->isOwner = $hashDoc->isOwner();

        // If viewer is the creator - don't count their view
        if (!$hashDoc->isOwner()) {
            $hashDoc->views++;
            $hashDoc->save();
        }

        $deleteMessage = $ttl ? 'delete_time' : 'delete_first';

        // Don't show 'other party' text to the 'other party'
        if ($hashDoc->isOwner() || $ttl) {
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

        $this->view->groups = $form->getDisplayGroups();
    }

    private function processTitle()
    {
        
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
        $hashDoc = Unsee_Mongo_Document_Hash::one(array('_id' => new MongoId($imgDoc->hashId)));

        !$hashDoc->isOwner() && $imgDoc->watermark();

        header('Content-type: ' . $imgDoc->type);
        header('Content-length: ' . strlen($imgDoc->data));
        print $imgDoc->data;
    }

    protected function getImageData($imgId)
    {
        $img = Unsee_Mongo_Document_Image::one(array('_id' => new MongoId($imgId)), array('_id'));
    }
}
