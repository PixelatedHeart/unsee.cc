<?php

class Zend_View_Helper_CssHelper extends Zend_View_Helper_Abstract
{

    function cssHelper()
    {
        $assetsDomain = Zend_Registry::get('config')->assetsDomain;

        $links = $this->view->headLink();
        $combining = Zend_Registry::get('config')->combineAssets;
        $urls = array();

        foreach ($links as $item) {
            if ($combining) {
                $urls[] = $item->href;
            } else {
                $item->href = $assetsDomain . $item->href;
            }
        }

        if ($combining) {
            $item->href = $assetsDomain . '/??' . implode(',', $urls);
            return $this->view->headLink()->itemToString($item);
        } else {
            return $this->view->headLink();
        }
    }
}
