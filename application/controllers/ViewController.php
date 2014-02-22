<?php

class ViewController extends Zend_Controller_Action
{

    protected $form;
    protected $hashDoc;

    public function init()
    {
        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex');
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');

        $this->view->headLink()->appendStylesheet('css/normalize.css');
        $this->view->headLink()->appendStylesheet('css/h5bp.css');
        $this->view->headLink()->appendStylesheet('css/view.css');
        $this->view->headLink()->appendStylesheet('css/subpage.css');

        $this->form = new Application_Form_Settings;
    }

    private function handleSettingsFormSubmit($form, $hashDoc)
    {
        if (!$hashDoc || !$hashDoc->isOwner()) {
            return false;
        }

        if ($form->isValid($_POST)) {
            $values = $form->getValues();

            // Changed value of TTL
            if (isset($values['ttl']) && $values['ttl'] != $hashDoc->ttl) {
                // Revert no_download to the value from DB, since there's no way
                // it could change
                unset($values['no_download']);
            }

            foreach ($values as $field => $value) {
                if ($field == 'strip_exif') {
                    continue;
                }

                $hashDoc->$field = $value;
            }
        }
    }

    public function indexAction()
    {
        // Hash (ababab)
        $hashString = $this->getParam('hash', false);

        if (!$hashString) {
            return $this->deletedAction();
        }

        // Get hash document
        $hashDoc = $this->hashDoc = new Unsee_Hash($hashString);
        $form = $this->form;

        // It was already deleted/did not exist/expired
        if (!$hashDoc->exists() || !$hashDoc->isViewable($hashDoc)) {
            return $this->deletedAction();
        }

        if ($this->getRequest()->isPost()) {
            $this->handleSettingsFormSubmit($form, $hashDoc);
        }

        // No use to do anything, page is not viewable
        if (!$hashDoc->isViewable($hashDoc)) {
            $hashDoc->delete();

            return $this->deletedAction();
        }

        $values = $hashDoc->export();
        // Populate form values
        $form->populate($values);
        // Disable image download by default
        $this->view->no_download = true;

        // Handle current request based on what settins are set
        foreach ($values as $key => $value) {
            $key = explode('_', $key);

            foreach ($key as &$itemItem) {
                $itemItem = ucfirst($itemItem);
            }

            $method = 'process' . implode('', $key);

            if (method_exists($this, $method) && !$this->$method()) {
                return $this->deletedAction();
            }
        }

        $this->view->isOwner = $hashDoc->isOwner();

        // If viewer is the creator - don't count their view
        if (!$hashDoc->isOwner()) {
            $hashDoc->views++;
        } else {
            // Owner - include config assets
            $this->view->headScript()->appendFile('js/view.js');
            $this->view->headLink()->appendStylesheet('css/settings.css');
        }

        // Don't show 'other party' text to the 'other party'
        if ($hashDoc->isOwner() || $hashDoc->ttl !== 'first') {
            if ($hashDoc->ttl === 'first') {
                $deleteTimeStr = '';
                $deleteMessageTemplate = 'delete_first';
            } else {
                $deleteTimeStr = $hashDoc->getTtlWords();
                $deleteMessageTemplate = 'delete_time';
            }

            $this->view->deleteTime = $this->view->translate($deleteMessageTemplate, array($deleteTimeStr));
        }

        $this->view->ttlSeconds = $hashDoc->getTtlSeconds();
        $this->view->images = $hashDoc->getImages();
        $this->view->form = $form;
        $this->view->groups = $form->getDisplayGroups();
    }

    private function processTitle()
    {
        if (!empty($this->hashDoc->title)) {
            $this->view->title = $this->hashDoc->title;
        }

        return true;
    }

    private function processDescription()
    {
        if (!empty($this->hashDoc->description)) {
            $this->view->description = $this->hashDoc->description;
        }

        return true;
    }

    private function processNoDownload()
    {
        if ($this->hashDoc->ttl === 'first') {
            $this->form->getElement('no_download')->setAttrib('disabled', 'disabled')->setAttrib('checked', 'checked');
        }

        $this->view->no_download = $this->hashDoc->no_download || $this->hashDoc->ttl === 'first';
        return true;
    }

    private function processAllowIp()
    {
        if (!empty($this->hashDoc->allow_ip) /* && !$hashDoc->isOwner() */) {
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
            return fnmatch($this->hashDoc->allow_ip, $ip);
        }

        return true;
    }

    private function processAllowDomain()
    {
        if (!empty($this->hashDoc->allow_domain) && !$this->hashDoc->isOwner()) {
            if (empty($_SERVER['HTTP_REFERER'])) {
                return false;
            }

            $expectedDomain = $this->hashDoc->allow_domain;

            $ref = parse_url($_SERVER['HTTP_REFERER']);

            if (!isset($ref['host'])) {
                return false;
            }

            $actualDomain = $ref['host'];

            if (!preg_match("~^([\w]+.)?$expectedDomain$~", $actualDomain)) {
                return false;
            }
        }

        return true;
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

        $imgDoc = new Unsee_Image($imageId);

        if (!$imgDoc) {
            $this->getResponse()->setHeader('Status', '204 No content');
            die();
        }

        $hashDoc = new Unsee_Hash($imgDoc->hash);

        if (!$hashDoc) {
            $imgDoc && $imgDoc->delete();
            $this->getResponse()->setHeader('Status', '204 No content');
            die();
        }

        $hashDoc->watermark_ip && $imgDoc->watermark();
        $hashDoc->comment && $imgDoc->comment($hashDoc->comment);

        $this->getResponse()->setHeader('Content-type', $imgDoc->type);

        print $imgDoc->getImageData();

        if (!$hashDoc->isViewable()) {
            $imgDoc->delete();
        }
    }
}
