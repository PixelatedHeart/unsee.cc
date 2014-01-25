<?php

class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{

    function javascriptHelper()
    {
        $assetsDomain = Zend_Registry::get('config')->assetsDomain;

        $links = $this->view->headScript();
        $combining = Zend_Registry::get('config')->combineAssets;
        $urls = array();

        foreach ($links as $item) {
            if ($combining) {
                $urls[] = $item->attributes['src'];
            } else {
                $item->attributes['src'] = $assetsDomain . $item->href;
            }
        }

        if ($combining) {
            $item->attributes['src'] = $assetsDomain . '/??' . implode(',', $urls);
            return $this->view->headScript()->itemToString($item, str_repeat(' ', 0), '', '');
        } else {
            return $this->view->headScript();
        }
    }
}
