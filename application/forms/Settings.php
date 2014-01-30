<?php

class Application_Form_Settings extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $settings = Zend_Registry::get('config')->settings;
        $lang = Zend_Registry::get('Zend_Translate');

        foreach ($settings as $section => $fields) {

            $groupFieldNames = array();
            $groupName = 'settings_' . $section;
            $groupTitle = $lang->translate($groupName);

            foreach ($fields as $name => $type) {

                $langStr = 'settings_' . $section . '_' . $name;

                $groupFieldNames[] = $elName = $section . '_' . $name;

                $fieldName = $lang->translate($langStr);
                $fieldCaption = $lang->translate($langStr . '_caption');
                $fieldHint = $lang->translate($langStr . '_hint');

                // Add an email element
                $element = $this->createElement(
                    $type,
                    $elName,
                    array(
                        'label'    => $fieldName,
                        'required' => false,
                        'filters'  => array('StringTrim')
                    )
                );
                $element->setAttrib('placeholder', $fieldHint);
                $element->setAttrib('id', $elName);

                $element->clearDecorators();
                $element->addDecorator('ViewHelper');
                $element->addDecorator('Label', array('title'=>$fieldCaption));

                $this->addElement($element);
            }

            $group = $this->addDisplayGroup($groupFieldNames, $groupName, array('legend'=>$groupTitle));

            foreach ($this->getDisplayGroups() as $group) {
                $group->clearDecorators();
                $group->addDecorator('SettingGroup');
                $group->addPrefixPath('Unsee_Form_Decorator_', 'Form/Decorator');
            }
        }
    }
}
