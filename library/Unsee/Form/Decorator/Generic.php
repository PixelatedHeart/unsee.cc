<?php

class Unsee_Form_Decorator_Generic extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {
        return $this->getElement()->renderViewHelper();
    }
}
