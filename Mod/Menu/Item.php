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
class Item extends \Tk\Object
{

    public $text = '';
    public $title = '';
    /**
     * @var \Tk\Url
     */
    public $url = null;
    public $target = '';
    public $cssClass = '';
    public $rel = '';
    public $icon = '';


    /**
     * @var array
     */
    public $children = array();

    /**
     * @var \Mod\Menu\Menu
     */
    public $ownerMenu = null;

    /**
     * @var \Mod\Menu\Item
     */
    public $parent = null;



    /**
     * Create a new menu Item
     *
     * @param string $text
     * @param \Tk\Url|string $url
     * @param string $icon
     * @return \Mod\Menu\Item
     */
    static function create($text = '', $url = null, $icon = '')
    {
        $obj = new self($text, $url, $icon);
        return $obj;
    }


    /**
     * The base menuItem object
     *
     * @param string $text
     * @param \Tk\Url|string $url
     * @param string $icon
     */
    protected function __construct($text = '', $url = null, $icon = '')
    {
        $this->text = $text;
        $this->title = $text;
        $this->url = \Tk\Url::create($url);
        $this->icon = $icon;
    }


    /**
     * Check if this is the menu tree's root item.
     *
     * @return bool
     */
    public function isRoot()
    {
        return ($this->parent == null && $this->ownerMenu == $this);
    }

    /**
     * Test if this node has children nodes.
     *
     * @return bool
     */
    public function hasChildren()
    {
        if (count($this->children)) {
            return true;
        }
        return false;
    }

    /**
     * Get this items children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get this items children
     *
     * @param array $children
     * @return Item
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Set the item title
     *
     * @param string $str
     * @return \Mod\Menu\Item
     */
    function setTitle($str)
    {
        $this->title = $str;
        return $this;
    }

    /**
     * Set the item target
     *
     * @param string $str
     * @return \Mod\Menu\Item
     */
    function setTarget($str)
    {
        $this->target = $str;
        return $this;
    }

    /**
     * Set the item rel
     *
     * @param string $str
     * @return \Mod\Menu\Item
     */
    function setRel($str)
    {
        $this->rel = $str;
        return $this;
    }

    /**
     * Set the Css Class
     *
     * @param string $str
     * @return \Mod\Menu\Item
     */
    function setCssClass($str)
    {
        $this->cssClass = $str;
        return $this;
    }

    /**
     * Set the owner menu for all nested items
     *
     * @param \Mod\Menu $menu
     * @return \Mod\Menu\Item
     */
    public function setOwnerMenu($menu)
    {
        $this->ownerMenu = $menu;
        foreach ($this->children as $child) {
            $child->setOwnerMenu($menu);
        }
        return $this;
    }

    /**
     * Set the parent and its children parents
     *
     * @param \Mod\Menu\Item $parent
     * @return \Mod\Menu\Item
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        foreach ($this->children as $child) {
            $child->setParent($parent);
        }
        return $this;
    }

    /**
     * find the first menu Item to conntain the matching href
     * Set $full to false to only search for the url path and not include the query string portion.
     *
     *
     * @param string $href
     * @param bool $full If true the full url and querystring will searched
     * @return \Mod\Menu\Item
     */
    public function findByHref($href, $full = true)
    {
        $cmp1 = \Tk\Url::create($href);
        $cmp2 = $this->url;
        if (!$full) {
            $cmp1->reset();
            $cmp2->reset();
        }
        if (($cmp1 && $cmp2) && $cmp1->toString() == $cmp2->toString()) {
            return $this;
        }

        foreach($this->children as $item) {
            $found = $item->findByHref($href, $full);
            if ($found) {
                return $found;
            }
        }
    }

    /**
     * Get a menu item by its text.
     *
     * @param string $text
     * @return \Mod\Menu\Item
     */
    public function findByText($text)
    {
        if ($this->text == $text) {
            return $this;
        }
        foreach($this->children as $item) {
            $found = $item->findByText($text);
            if ($found) {
                return $found;
            }
        }
    }

    /**
     * Get a menu item by its index.
     *
     * @param int $idx
     * @return \Mod\Menu\Item
     */
    public function findByIdx($idx = 0)
    {
        if (isset($this->children[$idx])) {
            return $this->children[$idx];
        }
    }

    /**
     * add an item
     *
     * @param \Mod\Menu\Item $item
     * @return \Mod\Menu\Item
     */
    public function addItem($item)
    {
        if (!is_array($item)) {
            $item = array($item);
        }
        foreach ($item as $i) {
            $i->setParent($this);
            $i->setOwnerMenu($this->ownerMenu);
            $this->children[] = $i;
        }
        return $item;
    }



    /**
     * reset the children array
     *
     * @return \Mod\Menu\Item
     */
    public function reset()
    {
        $this->children = array();
        return $this;
    }


    /**
     * Get the top most item
     *
     * @return \Mod\Menu\Item
     */
    public function current()
    {
        return end($this->children);
    }

    /**
     * get the size of the crumbs array
     *
     * @param bool $deep
     * @return int
     */
    public function count($deep = false)
    {
        $tot = count($this->children);
        if ($deep) {
            foreach ($this->children as $item) {
                $tot += $item->count(true);
            }
        }
        return $tot;
    }

    /**
     * Append an item
     *
     * @param \Mod\Menu\Item $item
     * @return \Mod\Menu\Item
     * @deprecated Use AddItem()
     */
    public function append($item)
    {
        return $this->addItem($item);
    }

    /**
     * Prepend Item
     *
     * @param \Mod\Menu\Item $item
     * @return \Mod\Menu\Item
     * @deprecated Use AddItem()
     */
    public function prepend($item)
    {
        return $this->addItem($item);
    }

    /**
     * insertBefore
     *
     * @param \Mod\Menu\Item $newItem
     * @param \Mod\Menu\Item $refNode (unused)
     * @return \Mod\Menu\Item
     * @deprecated Use AddItem()
     */
    public function insertBefore($newItem, $refNode = null)
    {
        return $this->addItem($newItem);
    }



}
