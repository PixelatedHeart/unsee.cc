<?php

class Unsee_Form_Decorator_Radio extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {
        $el = $this->getElement();
        $elName = $el->getName();

        $res = '';

        $options = $el->getMultiOptions();

        foreach ($options as $name => $title) {

            $lang = Zend_Registry::get('Zend_Translate');
            $captionStr = 'settings_' . $elName . '_'. $name .'_caption';

            $captionProp = '';

            if ($lang->isTranslated($captionStr)) {
                $captionProp = " title='".$lang->translate($captionStr)."' ";
            }

            $res .= "<div><input type='radio' name='{$elName}[]' id='{$elName}_$name'/><label $captionProp for='{$elName}_$name'>$title</label></div>";
        }

        return $res;
    }
}
