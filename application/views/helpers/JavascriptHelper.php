<?php

/**
 * Helper to optionally combine js files into one
 */
class Zend_View_Helper_JavascriptHelper extends Zend_View_Helper_Abstract
{

    function javascriptHelper()
    {
        $links = $this->view->headScript();
        $combining = Zend_Registry::get('config')->combineAssets;
        $urls = array();

        foreach ($links as $item) {
            if ($combining) {
                $urls[] = str_replace('js/', '', $item->attributes['src']);
            } else {
                $item->attributes['src'] = '/' . $item->attributes['src'];
            }
        }

        if ($combining) {
            $item->attributes['src'] = '/js/??' . implode(',', $urls);
            return $this->view->headScript()->itemToString($item, str_repeat(' ', 0), '', '');
        } else {
            return $this->view->headScript();
        }
    }
}
