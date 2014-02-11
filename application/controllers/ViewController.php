<?php

class ViewController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');

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
        if (!$hashDoc->isOwner()) {
            return false;
        }

        $form = new Application_Form_Settings();

        if ($form->isValid($_POST)) {
            $values = $form->getValues();

            foreach ($values as $field => $value) {
                if ($field == 'strip_exif') {
                    continue;
                }

                $hashDoc->$field = $value;
            }
            $hashDoc->save();
        }
    }

    public function indexAction()
    {
        // TODO: Refactor, too long

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

        // Populate form values
        foreach ($hashDoc as $key => $value) {
            $el = $form->getElement($key);

            if ($el && strlen($value)) {
                $el->setValue($value);
            }
        }

        $this->view->no_download = true;

        // Handle current request based on what settins are set
        $props = $hashDoc->getPropertyKeys();

        foreach ($props as $item) {
            $item = explode('_', $item);

            foreach ($item as &$itemItem) {
                $itemItem = ucfirst($itemItem);
            }

            $method = 'process' . implode('', $item);

            if (method_exists($this, $method)) {
                if (!$this->$method($hashDoc)) {
                    return $this->deletedAction();
                }
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
        } else {
            $this->view->headScript()->appendFile('js/view.js');
            $this->view->headLink()->appendStylesheet('css/settings.css');
        }

        $deleteMessage = $ttl ? 'delete_time' : 'delete_first';
        $secondsLeft = $ttd - time();

        $times = array();
        $timeStrings = array();
        $times['minute'] = 60;
        $times['hour'] = $times['minute'] * 60;
        $times['day'] = $times['hour'] * 24;

        $times = array_reverse($times);

        $lang = Zend_Registry::get('Zend_Translate');

        foreach ($times as $key => &$time) {
            $res = floor($secondsLeft / $time);
            $secondsLeft -= $res * $time;

            $langStr = $key;

            $modRes = $res % 10;

            if ($modRes === 1) {
                $langStr .= '_one';
            } elseif ($modRes > 1 && $modRes < 5) {
                $langStr .= '_couple';
            } else {
                $langStr .= '_many';
            }

            if ($res) {
                $timeStrings[] = $res . ' ' . $lang->translate($langStr);
            }
        }

        $timeStrings = array_filter($timeStrings);
        $last = array_pop($timeStrings);

        $deleteTime = '';
        if (count($timeStrings)) {
            $deleteTime = implode(', ', $timeStrings) . ' ' . $lang->translate('and') . ' ';
        }
        $deleteTime .= $last;

        // Don't show 'other party' text to the 'other party'
        if ($hashDoc->isOwner() || $ttl) {
            $this->view->deleteTime = $this->view->translate($deleteMessage, array($deleteTime));
        }

        $imagesList = Unsee_Mongo_Document_Image::all(array('hashId' => new MongoId($hashDoc->_id)));

        $this->view->images = array();

        $secureLinkTtl = 2; // image links would live for this number of seconds
        if (!$hashDoc->no_download) {
            end(Unsee_Mongo_Document_Hash::$_ttlTypes);
            $secureLinkTtl = key(Unsee_Mongo_Document_Hash::$_ttlTypes);
            reset(Unsee_Mongo_Document_Hash::$_ttlTypes);
        }

        $key = 0;
        foreach ($imagesList as $imageDoc) {

            $imageId = (string) $imageDoc->_id;

            $imageDoc->ticketTtd = $ticketTtd = $_SERVER['REQUEST_TIME'] + $key++ + $secureLinkTtl; // Each image would be loaded a second later
            // Preparing a hash for nginx's secure link
            $md5 = base64_encode(md5($imageId . $ticketTtd, true));
            $md5 = strtr($md5, '+/', '-_');
            $md5 = str_replace('=', '', $md5);
            $imageDoc->md5 = $md5;

            $this->view->images[$imageId] = $imageDoc;
        }

        $this->view->form = $form;
        $this->view->groups = $form->getDisplayGroups();
    }

    private function processTitle($hashDoc)
    {
        if (!empty($hashDoc->title)) {
            $this->view->title = $hashDoc->title;
        }

        return true;
    }

    private function processDescription($hashDoc)
    {
        if (!empty($hashDoc->description)) {
            $this->view->description = $hashDoc->description;
        }

        return true;
    }

    private function processNoDownload($hashDoc)
    {
        $this->view->no_download = !empty($hashDoc->no_download);
        return true;
    }

    private function processAllowIp($hashDoc)
    {
        if (!empty($hashDoc->allow_ip) /* && !$hashDoc->isOwner() */) {
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
            return fnmatch($hashDoc->allow_ip, $ip);
        }

        return true;
    }

    private function processAllowDomain($hashDoc)
    {
        if (!empty($hashDoc->allow_domain) && !$hashDoc->isOwner()) {
            if (empty($_SERVER['HTTP_REFERER'])) {
                return false;
            }

            $expectedDomain = $hashDoc->allow_domain;

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

        $imgDoc = Unsee_Mongo_Document_Image::one(array('_id' => new MongoId($imageId)));
        $hashDoc = Unsee_Mongo_Document_Hash::one(array('_id' => new MongoId($imgDoc->hashId)));

        $hashDoc->watermark_ip && $imgDoc->watermark();

        header('Content-type: ' . $imgDoc->type);
        //header('Content-length: ' . $imgDoc->size); // TODO: fix it, it doesn't work
        die($imgDoc->data->bin);
    }

    protected function getImageData($imgId)
    {
        $img = Unsee_Mongo_Document_Image::one(array('_id' => new MongoId($imgId)), array('_id'));
    }
}
