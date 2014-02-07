<?php

class Application_Form_Contact extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->setAction('');

        $this->clearDecorators();
        $this->addPrefixPath('Unsee_Form_', 'Unsee/Form/');

        $select = $this->createElement('select', 'type');
        $select->clearDecorators();
        $select->setLabel('message_type');
        $select->addDecorator('Label');
        $select->addDecorator('ViewHelper');
        $select->addDecorator('Generic');

        $options = array(
            'general_questions' => 'general_questions',
            'report_bug'     => 'report_bug',
            'suggest_feature' => 'suggest_feature',
        );

        $select->setMultiOptions($options);
        $select->addValidator('StringLength');
        $select->addFilters(array('StringTrim', 'StripTags'));
        $select->setRequired();
        $this->addElement($select);



        $name = $this->createElement('text', 'name');
        $name->clearDecorators();
        $name->setLabel('name');
        $name->addDecorator('Label');
        $name->addDecorator('ViewHelper');
        $name->addDecorator('Generic');
        $name->addValidator('stringLength');
        $name->addFilters(array('StringTrim', 'StripTags'));
        $name->setRequired();
        $this->addElement($name);



        $email = $this->createElement('text', 'email');
        $email->clearDecorators();
        $email->setLabel('email');
        $email->addDecorator('Label');
        $email->addDecorator('ViewHelper');
        $email->addDecorator('Generic');
        $email->addValidator('EmailAddress');
        $email->addFilters(array('StringTrim', 'StripTags'));
        $email->setRequired();
        $this->addElement($email);


        $message = $this->createElement('textarea', 'message');
        $message->clearDecorators();
        $message->addDecorator('ViewHelper');
        $message->addDecorator('Generic');
        $message->addValidator('stringLength');
        $message->addFilters(array('StripTags'));
        $message->setRequired();
        $message->setAttrib('rows', 7);
        $this->addElement($message);

        $submit = $this->createElement('submit', 'send');
        $submit->clearDecorators();
        $submit->addDecorator('ViewHelper');
        $submit->addDecorator('Generic');
        $submit->setRequired(false);
        $submit->setAttrib('id', 'sendMessage');
        
        $this->addElement($submit);
        
    }
}
