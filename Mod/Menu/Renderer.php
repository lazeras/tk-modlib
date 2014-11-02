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
class Renderer extends \Mod\Renderer
{

    /**
     * @var \Mod\Menu\Menu
     */
    protected $menu = null;

    /**
     * @var int
     */
    protected $maxDepth = 3;

    public $activeClass = 'active';

    public $dropdown = array(
        'ul' => array(
            0 => array('class' => 'nav'),
            1 => array('class' => 'dropdown-menu'),
            2 => array('class' => 'dropdown-menu')
        ),
        'li' => array(
            0 => array('class' => 'dropdown'),
            1 => array('class' => 'dropdown-submenu')
        ),
        'a' => array(
            0 => array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', '_icon' => '<b class="caret"></b>')
        )
    );



    /**
     * constructor
     *
     * @param \Mod\Menu\Menu $menu
     * @param array $params
     */
    public function __construct(Menu $menu, $params = array())
    {
        $this->menu = $menu;
        foreach ($params as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    /**
     *
     * @return \Mod\Menu\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }



    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();
        if (!$this->menu->hasChildren()) {
            return;
        }

        if ($this->menu->showTitle && $template->keyExists('var', 'title')) {
            $template->setChoice('title');
            $title = sprintf('<%s>%s</%s>', $this->menu->titleTag, $this->menu->title, $this->menu->titleTag);
            $template->replaceHtml('title', $title);
        }

        $ul = $this->iterate($this->menu->children);

        $class = preg_replace('/[^a-z0-9_-]/i', '', $this->menu->title);
        $ul->addClass('ul', $class);
        if ($this->menu->cssClass) {
            $ul->addClass('ul', $this->menu->cssClass);
        }
        $ul->appendRepeat();
    }

    /**
     *
     * @param array $list
     * @param int $nest
     * @return \Dom\Template
     */
    protected function iterate($list, $nest=0)
    {
        if ($this->maxDepth > 0 && $nest >= $this->maxDepth) {
            return;
        }
        $ul = $this->getTemplate()->getRepeat('ul');

        if (isset($this->dropdown['ul'][$nest])) {
            foreach($this->dropdown['ul'][$nest] as $attr => $val) {
                $ul->setAttr('ul', $attr, $val);
            }
        }

        /* @var $item Item */
        foreach ($list as $item) {
            $request = $this->getUri();
            $url = \Tk\Url::create($item->url);

            $li = $ul->getRepeat('li');
            if ($item->findByHref($request, true)) {
                $li->addClass('li', $this->activeClass);
            }

            if ($item->cssClass) {
                $li->addClass('li', $item->cssClass);
            }

            if ($item instanceof \Mod\Menu\Divider) {
                $li->insertText('li', '');
            } else if ($item instanceof \Mod\Menu\Header) {
                $li->insertText('li', $item->text);
            } else {
                if ($item->title) {
                    $li->setAttr('a', 'title', $item->title);
                }
                if ($item->target) {
                    $li->setAttr('a', 'target', $item->target);
                }
                if ($item->rel) {
                    $li->setAttr('a', 'rel', $item->rel);
                }

                if ($item->icon) {
                    $li->addClass('icon', $item->icon);
                    $li->setChoice('icon');
                }

                $li->insertText('a-text', $item->text);
                $li->setAttr('a', 'href', $url->toString());
            }

            if ($item->hasChildren()) {
                if (isset($this->dropdown['li'][$nest])) {
                    foreach($this->dropdown['li'][$nest] as $attr => $val) {
                        $li->setAttr('li', $attr, $val);
                    }
                }

                if (isset($this->dropdown['a'][$nest])) {
                    if (isset($this->dropdown['a'][$nest]['_icon'])) {
                        $li->insertHtml('a-text', $item->text . $this->dropdown['a'][$nest]['_icon']);
                    }
                    foreach($this->dropdown['a'][$nest] as $attr => $val) {
                        if ($attr == '_icon') continue;
                        $li->setAttr('a', $attr, $val);
                    }
                }
                $ul2 = $this->iterate($item->children, $nest+1);
                //$ul2->addClass('ul', $this->subMenuClass);
                if (isset($this->dropdown['ul'][$nest+1])) {
                    foreach($this->dropdown['ul'][$nest+1] as $attr => $val) {
                        $ul2->setAttr('ul', $attr, $val);
                    }
                }
                $li->appendTemplate('li', $ul2);
            }
            $li->appendRepeat();
        }

        return $ul;
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
<div class="tk-menu">
  <div var="title" choice="title"></div>
  <ul var="ul" repeat="ul">
    <li var="li" repeat="li"><a href="#" var="a"><i choice="icon" var="icon" class=""></i> <span var="a-text"></span></a></li>
  </ul>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }


}
