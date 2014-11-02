<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Mod\Menu;

/**
 *
 *
 * @package Mod\Menu
 */
class Menu extends Item
{
    /**
     * Display menu items and all child nodes
     */
    const MODE_NORMAL = 'normal';
    /**
     * Display only child nodes when parent node matched current url.
     * Useful for side Nav menus that change on a per url basis
     */
    const MODE_SLAVE = 'slave';

    public $mode = self::MODE_NORMAL;
    public $titleTag = 'h3';
    public $showTitle = false;

    /**
     * Should the menu render if no items exist
     * @var type
     */
    public $showEmpty = false;

    protected $renderVar = \Mod\Page::VAR_PAGE_SIDE_NAV_1;

    protected $rendererClass = '\Mod\Menu\Renderer';
    /**
     *
     * @var \Mod\Menu\Renderer
     */
    protected $renderer = null;


    /**
     * Create a new menu
     *
     * @param string $title
     * @param \Tk\Url $url
     * @param string $icon
     * @return \Mod\Menu\Menu
     */
    static function createMenu($title, $url = null, $icon = '')
    {
        $obj = new self($title, $url, $icon);
        $obj->ownerMenu = $obj;
        return $obj;
    }

    public function __wakeup()
    {
        $this->renderer = null;
    }

    public function __sleep()
    {
        $this->renderer = null;
        return array('homeTitle', 'mode', 'titleTag', 'showTitle', 'renderVar', 'rendererClass',
            'text', 'title', 'url', 'target', 'cssClass', 'rel', 'icon', 'children', 'ownerMenu', 'parent', 'instanceId', 'observable', 'notifyEnabled', 'showEmpty');
    }


    /**
     *
     * @param array $params
     * @return \Mod\Renderer
     */
    public function getRenderer($params = array())
    {
        if (!$this->renderer) {
            $class = $this->rendererClass;
            $this->renderer = new $class($this, $params);
        }
        return $this->renderer;
    }

    /**
     * Set the class of the renderer for this menu
     *
     * @param string $class
     * @return \Mod\Menu\Menu
     */
    public function setRendererClass($class)
    {
        $this->rendererClass = $class;
        return $this;
    }

    /**
     * Set the var that the menu will be rendered to
     * Default \Mod\Module::VAR_PAGE_SIDE_NAV_1
     *
     * @param string $var
     * @return \Mod\Menu\Menu
     */
    public function setRenderVar($var)
    {
        $this->renderVar = $var;
        return $this;
    }

    /**
     * Get the renderVar
     *
     * @return string
     */
    public function getRenderVar()
    {
        return $this->renderVar;
    }

    /**
     * Should the renderer display the title of the menu
     * Default: true
     *
     * @param bool $b
     * @return \Mod\Menu\Menu
     */
    public function showTitle($b = true)
    {
        $this->showTitle = $b;
        return $this;
    }

    /**
     * Defaults to a &lt;h3&gt;Title&lt;/h3&gt; tag
     *
     * @param string $tag
     * @return \Mod\Menu\Menu
     */
    public function setTitleTag($tag)
    {
        $this->titleTag = $tag;
        return $this;
    }

    /**
     * see class constants
     *  o self::MODE_NORMAL
     *  o self::MODE_SLAVE
     *
     * @param string $mode
     * @return \Mod\Menu\Menu
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

}

