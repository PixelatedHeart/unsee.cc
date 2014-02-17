<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->headScript()->appendFile('js/vendor/jquery-1.8.3.min.js');
        $this->view->headScript()->appendFile('js/vendor/modernizr-2.6.2.min.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.iframe-transport.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.ui.widget.js');
        $this->view->headScript()->appendFile('js/vendor/jquery.fileupload.js');
        $this->view->headScript()->appendFile('js/plugins.js');
        $this->view->headScript()->appendFile('js/main.js');

        $this->view->headLink()->appendStylesheet('css/normalize.css');
        $this->view->headLink()->appendStylesheet('css/h5bp.css');
        $this->view->headLink()->appendStylesheet('css/main.css');
        $this->view->headLink()->appendStylesheet('css/subpage.css');
    }

    public function indexAction()
    {
        $form = new Application_Form_Contact();
        $this->view->form = $form;
    }

    public function sendAction()
    {
        $res = new stdClass();

        $form = new Application_Form_Contact();

        if ($form->isValid($_POST)) {
            $res->success = true;

            $lang = Zend_Registry::get('Zend_Translate');

            $subject = $lang->translate($form->type->getValue());
            $message = $form->message->getValue();
            $fromName = $form->name->getValue();
            $fromEmail = $form->email->getValue();

            $this->sendMail($subject, $message, $fromName, $fromEmail);
            // Send email
        } else {
            $res->errors = array_keys($form->getMessages());
        }

        $this->_helper->json->sendJson($res);
    }

    private function sendMail($subject, $message, $fromName, $fromEmail)
    {
        $mail = new Zend_Mail('UTF-8');
        $mail->addTo('goreanski@gmail.com');
        $mail->addBcc('iaroslav.svet@gmail.com');
        $mail->setSubject($subject);
        $mail->setBodyText($message . PHP_EOL . 'Sender IP: ' . $_SERVER['REMOTE_ADDR']);
        $mail->setFrom('mailer@umkus.com', 'Unsee.cc');
        $mail->setReplyTo($fromEmail, $fromName);
        $mail->setDefaultTransport(new Zend_Mail_Transport_Sendmail());

        try {
            return $mail->send();
        } catch (Exception $e) {
            
        }
    }
}
