<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Menu;

/**
 *
 *
 * @package Mod\Menu
 */
class CrumbsRenderer extends Renderer
{

    public function getInsertMethod()
    {
        return \Mod\Module::INS_REPLACE;
    }

    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();
        if (!$this->menu->count()) {
            return;
        }
        $stack = $this->menu->getChildren();
        $i = 0;
        /* @var $item Item */
        foreach ($stack as $item) {
            $repeat = $template->getRepeat('li');
            $repeat->insertText('a_text', ' '.$item->text);
            $repeat->setAttr('a', 'href', $item->url);
            $repeat->setAttr('a', 'title', $item->title);
            $css = preg_replace('/\s/', '-', strtolower($item->text));
            $repeat->addClass('li', $css);
            if ($item->icon) {

                $repeat->addClass('icon', $item->icon);
                $repeat->setChoice('icon');
            }

            if (($i+1) == count($stack)) {
                $repeat->setAttr('a', 'href', '#');
                $repeat->addClass('li', 'active');
            }
            $repeat->appendRepeat();
            $i++;
        }
    }


    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<ul class="breadcrumb" var="ul">
  <li var="li" repeat="li"><a var="a"><i var="icon" choice="icon"></i><span var="a_text"></span></a></li>
</ul>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }


}
