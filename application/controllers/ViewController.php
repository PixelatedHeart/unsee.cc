<?php

/**
 * Controller for the image viewing page
 */
class ViewController extends Zend_Controller_Action
{

    /**
     * @var Zend_Form   Settings form
     */
    protected $form;

    /**
     * @var Unsee_Hash  Hash instance
     */
    protected $hashDoc;

    public function init()
    {
        // This page should never be indexed by robots
        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex');
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile('js/view.js');
        $this->view->headScript()->appendFile('js/chat.js');

        $this->view->headLink()->appendStylesheet('css/normalize.css');
        $this->view->headLink()->appendStylesheet('css/h5bp.css');
        $this->view->headLink()->appendStylesheet('css/view.css');
        $this->view->headLink()->appendStylesheet('css/subpage.css');
        $this->view->headLink()->appendStylesheet('css/chat.css');

        // Preheating the form
        $this->form = new Application_Form_Settings;
    }

    /**
     * Process image sharing settings form
     * @param Zend_Form $form
     * @param Unsee_Hash $hashDoc
     * @return boolean
     */
    private function handleSettingsFormSubmit($form, $hashDoc)
    {
        // Don't try to process the form if the hash was deleted or the viewer is not the author
        if (!$hashDoc || !Unsee_Session::isOwner($hashDoc)) {
            return false;
        }

        if ($form->isValid($_POST)) {
            $values = $form->getValues();

            // Changed value of TTL
            if (isset($values['ttl']) && $hashDoc->ttl === Unsee_Hash::$ttlTypes[0]) {
                // Revert no_download to the value from DB, since there's no way
                // it could have changed. It's disabled when ttl == 'first'.
                unset($values['no_download']);
            }

            $expireAt = false;

            // Apply values from form to hash in Redis
            foreach ($values as $field => $value) {
                if ($field == 'strip_exif') {
                    // But skip strip_exif, since it's always on
                    continue;
                }

                if ($field === 'ttl') {
                    // Delete after view?
                    if ($value == Unsee_Hash::$ttlTypes[0]) {
                        $hashDoc->max_views = 1;
                        $expireAt = $hashDoc->timestamp + Unsee_Redis::EXP_DAY;
                        // Set to expire within a day after upload
                    } else {
                        $amount = array_search($value, Unsee_Hash::$ttlTypes);
                        $hashDoc->max_views = 0;
                        $expireAt = $hashDoc->timestamp + $amount;
                    }
                }

                $hashDoc->$field = $value;
            }

            if ($expireAt) {
                $hashDoc->expireAt($expireAt);
            }
        }
    }

    /**
     * Default controller for image view page
     * @return boolean
     */
    public function indexAction()
    {
        // Hash (bababa)
        $hashString = $this->getParam('hash', false);

        if (!$hashString) {
            return $this->deletedAction();
        }

        // Get hash document
        $hashDoc = $this->hashDoc = new Unsee_Hash($hashString);
        $form = $this->form;

        $block = new Unsee_Block($hashDoc->key);
        $sessionId = Unsee_Session::getCurrent();

        /**
         * "Block" cookie detected. This means that viewer performed one of the restricred actions, like
         * opening a web developer tools (Firebug), pressed the print screen button, etc.
         */
        if (isset($_COOKIE['block'])) {
            // Remove the cookie
            setcookie('block', null, 1, '/' . $hashDoc->key . '/');
            // Register a block flag for current session
            $block->$sessionId = time();
            // Act as if the image was deleted
            return $this->deletedAction();
        }

        // The block flag was previously set for the current session
        if (isset($block->$sessionId)) {
            return $this->deletedAction();
        }

        // It was already deleted/did not exist/expired
        if (!$hashDoc->exists() || !$hashDoc->isViewable($hashDoc)) {
            return $this->deletedAction();
        }

        // Handle image settings form submission
        if ($this->getRequest()->isPost()) {
            $this->handleSettingsFormSubmit($form, $hashDoc);
        }

        // Check again
        // It was already deleted/did not exist/expired
        if (!$hashDoc->exists() || !$hashDoc->isViewable($hashDoc)) {
            return $this->deletedAction();
        }

        // No use to do anything, page is not viewable for one of the reasons
        if (!$hashDoc->isViewable($hashDoc)) {
            $hashDoc->delete();
            return $this->deletedAction();
        }

        // Getting an array of hash settings
        $values = $hashDoc->export();
        // Populate form values
        $form->populate($values);
        // Disable image download by default
        $this->view->no_download = true;

        $images = $hashDoc->getImages();
        // Creating a set of "tickets" to view images related to current hash
        $ticket = new Unsee_Ticket();

        // Create a view "ticket" for every image of a hash
        foreach ($images as $image) {
            $ticket->issue($image->key);
        }

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

        $this->view->isOwner = Unsee_Session::isOwner($hashDoc);

        // If viewer is the creator - don't count their view
        if (!Unsee_Session::isOwner($hashDoc)) {
            $hashDoc->views++;

            // Reached max views for this hash
            if ($hashDoc->max_views && $hashDoc->views >= $hashDoc->max_views) {
                // Remove the hash in a while for the images to be displayed
                $hashDoc->expireAt(time() + 30);
            }
        } else {
            // Owner - include extra webpage assets
            $this->view->headScript()->appendFile('js/settings.js');
            $this->view->headLink()->appendStylesheet('css/settings.css');
        }

        // Don't show the 'other party' text for the 'other party'
        if (Unsee_Session::isOwner($hashDoc) || $hashDoc->ttl !== Unsee_Hash::$ttlTypes[0]) {
            if ($hashDoc->ttl === Unsee_Hash::$ttlTypes[0]) {
                $deleteTimeStr = '';
                $deleteMessageTemplate = 'delete_first';
            } else {
                $deleteTimeStr = $hashDoc->getTtlWords();
                $deleteMessageTemplate = 'delete_time';
            }

            $this->view->deleteTime = $this->view->translate($deleteMessageTemplate, array($deleteTimeStr));
        }

        // Cookie check vould be passed to the image view controller below to 
        // make sure the page was opened in a browser
        $this->view->cookieCheck = md5(Unsee_Session::getCurrent() . $hashDoc->key);
        $this->view->images = $images;
        $this->view->groups = $form->getDisplayGroups();

        return true;
    }

    public function noContentAction()
    {
        $this->getResponse()->setHeader('Status', '204 No content');
        die();
    }

    /**
     * Sets the hash title if available
     * @return boolean
     */
    private function processTitle()
    {
        if (!empty($this->hashDoc->title)) {
            $this->view->title = $this->hashDoc->title;
        }

        return true;
    }

    /**
     * Sets the hash description if available
     * @return boolean
     */
    private function processDescription()
    {
        if (!empty($this->hashDoc->description)) {
            $this->view->description = $this->hashDoc->description;
        }

        return true;
    }

    /**
     * Sets up things affected by the no_download setting
     * @return boolean
     */
    private function processNoDownload()
    {
        // If it's a one-time view image
        if ($this->hashDoc->ttl === Unsee_Hash::$ttlTypes[0]) {
            // Disable the "no download" checkbox
            // And set it to "checked"
            $this->form->getElement('no_download')->setAttrib('disabled', 'disabled')->setAttrib('checked', 'checked');
        }

        // Don't allow download if the setting is set accordingly or the image is a one-timer
        $this->view->no_download = $this->hashDoc->no_download || $this->hashDoc->ttl === Unsee_Hash::$ttlTypes[0];
        return true;
    }

    /**
     * Returns true if IP is allowed or the allow_ip setting is not set
     * @return boolean
     */
    private function processAllowIp()
    {
        if (!empty($this->hashDoc->allow_ip) && !Unsee_Session::isOwner($this->hashDoc)) {
            $ip = $this->getRequest()->getServer('REMOTE_ADDR');
            return fnmatch($this->hashDoc->allow_ip, $ip);
        }

        return true;
    }

    /**
     * Returns true if the referring domain is not set or equals the one from the allow_domain setting
     * @return boolean
     */
    private function processAllowDomain()
    {
        if (!empty($this->hashDoc->allow_domain) && !Unsee_Session::isOwner($this->hashDoc)) {
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

    /**
     * Action for deleted hashes, displays a "Deleted" message
     * @return bool
     */
    public function deletedAction()
    {
        $this->render('deleted');
        return $this->getResponse()->setHttpResponseCode(410);
    }

    /**
     * Action that handles image requests
     */
    public function imageAction()
    {
        // We would just print out the image, no need for the renderer
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // Getting request params
        $imageId = $this->getParam('id');
        $ticket = $this->getParam('ticket');
        $time = $this->getParam('time');

        // Dropping request if params are not right or the image is too old
        if (!$imageId || !$ticket || !$time || $time < time()) {
            $this->noContentAction();
        }

        // Fetching the image Redis hash
        $imgDoc = new Unsee_Image($imageId);

        // It wasn't there
        if (!$imgDoc) {
            $this->noContentAction();
        }

        list($hashStr) = explode('_', $imgDoc->key);

        if (!$hashStr) {
            $this->noContentAction();
        }

        // Fetching the parent hash
        $hashDoc = new Unsee_Hash($hashStr);

        // It didn't exist
        if (!$hashDoc) {
            // But the image did, delete it
            $imgDoc && $imgDoc->delete();
            $this->noContentAction();
        }

        /**
         * Restricting image download also means that it has to requested by the page, e.g. no
         * direct access. Direct access means no referrer.
         */
        if ($hashDoc->no_download && empty($_SERVER['HTTP_REFERER'])) {
            $this->noContentAction();
        }

        // Fetching ticket list for the hash, it should have a ticket for the requested image
        $ticketDoc = new Unsee_Ticket();

        // Looks like a gatecrasher, no ticket and image is not allowed to be downloaded directly
        if (!$ticketDoc->isAllowed($imgDoc) && ($hashDoc->no_download || $hashDoc->ttl === 'first')) {
            // Delete the ticket
            $ticketDoc->invalidate($imgDoc);
            $this->noContentAction();
        } else {
            // Delete the ticket
            $ticketDoc->invalidate($imgDoc);
        }

        // Watermark viewer's IP if required
        $hashDoc->watermark_ip && $imgDoc->watermark();

        // Embed comment if required
        $hashDoc->comment && $imgDoc->comment($hashDoc->comment);

        $this->getResponse()->setHeader('Content-type', $imgDoc->type);

        // Dump image data
        print $imgDoc->content;

        // The hash itself was already outdated for one of the reasons.
        if (!$hashDoc->isViewable()) {
            // This means the image should not be avaiable, so delete it
            $imgDoc->delete();
        }
    }
}
