<?php

class Unsee_Form_Element_Select_Model_Delete extends Unsee_Form_Element_Select_Model_Abstract
{

    static public function getValues(Zend_Translate $lang)
    {
        $vars = array('first', 'hour', 'day', 'three');
        $values = array();

        foreach ($vars as $item) {

            $elLangString = 'settings_delete_when_' . $item;

            $itemTrans = $lang->translate('settings_delete_when_');

            if ($lang->isTranslated($elLangString)) {
                $values[$item] = $lang->translate($elLangString);
            }
        }

        return $values;
    }
}
