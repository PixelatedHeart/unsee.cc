<?php

/**
 * Form decorator to correctly render radio button list
 */
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
            // TODO: "delete" is hardcoded, dynamically get group of the field here
            $captionStr = 'settings_delete_' . $elName . '_' . $name . '_caption';

            $captionProp = $selectedProp = '';

            if ($lang->isTranslated($captionStr)) {
                $captionProp = " title='" . $lang->translate($captionStr) . "' ";
            }

            if ($name === $el->getValue()) {
                $selectedProp = "checked='checked'";
            }

            $res .= "<div><input type='radio' name='{$elName}' id='{$elName}_$name' value='$name' $selectedProp/>".
                    "<label $captionProp for='{$elName}_$name'>$title</label></div>";
        }

        return $res;
    }
}
