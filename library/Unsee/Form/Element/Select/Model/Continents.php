<?php

class Unsee_Form_Element_Select_Model_Continents extends Unsee_Form_Element_Select_Model_Abstract
{

    static public function getValues(Zend_Translate $lang)
    {
        return Zend_Locale::getTranslationList('Territory', $lang->getLocale(), 1);
    }
}
