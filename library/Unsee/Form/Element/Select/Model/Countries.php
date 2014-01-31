<?php

class Unsee_Form_Element_Select_Model_Countries extends Unsee_Form_Element_Select_Model_Abstract
{

    static public function getValues(Zend_Translate $lang)
    {
        $countries = Zend_Locale::getTranslationList('Territory', $lang->getLocale(), 2);
        $continents = Zend_Locale::getTranslationList('Territory', $lang->getLocale(), 1);

        array_unshift($countries, current($continents));

        return $countries;
    }
}
