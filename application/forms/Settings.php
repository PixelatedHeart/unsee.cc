<?php

class Application_Form_Settings extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->setAction('/settings/');

        $settings = Zend_Registry::get('config')->settings;
        $lang = Zend_Registry::get('Zend_Translate');

        $continents = Zend_Locale::getTranslationList('Territory', $lang->getLocale(), 1);
        $countries = Zend_Locale::getTranslationList('Territory', $lang->getLocale(), 2);

        array_unshift($countries, current($continents));

        foreach ($settings as $section => $fields) {
            $groupFieldNames = array();
            $groupName = 'settings_' . $section;
            $groupTitle = $lang->translate($groupName);

            foreach ($fields as $name => $params) {

                $params = $params->toArray();

                if (empty($params['type'])) {
                    throw new Exception('Type parameter is required for field "' . $name . '"');
                }

                $type = $params['type'];

                $langStr = 'settings_' . $section . '_' . $name;
                $groupFieldNames[] = $elName = $name;

                $element = $this->createElement($type, $elName);
                $element->setLabel($lang->translate($langStr));

                if ($type === 'checkbox' && !empty($params['checked']) && $params['checked'] === 'true') {
                    $element->setAttrib('checked', 'checked');
                }

                if (!empty($params['model'])) {
                    $modelClass = 'Unsee_Form_Element_Select_Model_' . $params['model'];

                    if (!class_exists($modelClass)) {
                        throw new Exception('Model class "' . $params['model'] . '" was not found');
                    }

                    $element->setMultiOptions($modelClass::getValues($lang));
                }

                $hintStr = $langStr . '_hint';
                if ($lang->isTranslated($hintStr)) {
                    $element->setAttrib('placeholder', $lang->translate($hintStr));
                }

                $element->setAttrib('id', $elName);

                $element->clearDecorators();
                $element->addDecorator('Label', array('title' => $lang->translate($langStr . '_caption')));
                $element->addPrefixPath('Unsee_Form_', 'Unsee/Form/');
                $element->addDecorator('ViewHelper');

                if ($type === 'radio') {
                    $element->setRequired(true);
                    $element->addDecorator('Radio');
                } else {
                    $element->addDecorator('Generic');
                }

                $this->addElement($element);
            }

            $group = $this->addDisplayGroup($groupFieldNames, $groupName, array('legend' => $groupTitle));

            foreach ($this->getDisplayGroups() as $group) {
                $group->clearDecorators();
                $group->addDecorator('SettingGroup');
                $group->addPrefixPath('Unsee_Form_Decorator_', 'Form/Decorator');
            }
        }
    }
}
