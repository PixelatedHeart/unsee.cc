<?php

/**
 * Overriding form decorator to render just the view helper by default, label is skipped
 */
class Unsee_Form_Decorator_Generic extends Zend_Form_Decorator_Abstract
{

    public function render($content)
    {
        return $this->getElement()->renderViewHelper();
    }
}
