<?php

/**
 * Helper to optionally combine css files into one
 */
class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract
{

    function cssHelper()
    {
        $links = $this->view->headLink();
        $combining = Zend_Registry::get('config')->combineAssets;
        $urls = array();

        foreach ($links as $item) {
            if ($combining) {
                $urls[] = str_replace('css/', '', $item->href);
            } else {
                $item->href = '/' . $item->href;
            }
        }

        if ($combining) {
            $item->href = '/css/??' . implode(',', $urls);
            return $this->view->headLink()->itemToString($item);
        } else {
            return $this->view->headLink();
        }
    }
}
