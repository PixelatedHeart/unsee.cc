<?php

class SettingsController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        $form = new Application_Form_Settings();
        $fields = array();
        $groups = $form->getDisplayGroups();
    }
}