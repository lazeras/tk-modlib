<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Mod\Menu;

/**
 * Crumb menu should have no nested items and only
 * one level of children to manage.
 *
 *
 */
class Crumbs extends Menu
{

    /**
     * Crumbs will be locked down after the init method is called.
     * @var bool
     */
    private $locked = false;




    /**
     * Create a new menu
     *
     * @param string $title
     * @param string|\Tk\Url $url
     * @param string $icon
     * @return \Mod\Menu\Crumbs
     */
    static function createCrumbMenu($title = 'Dashboard', $url = null, $icon = 'fa fa-dashboard')
    {
        if (!$url)
            $url = \Tk\Url::createHomeUrl('/index.html');
        $obj = new self($title, $url, $icon);
        $obj->showTitle(false);
        $obj->ownerMenu = $obj;
        return $obj;
    }

    /**
     * This should be called on each request
     * Somewhere in the initiation of the app.
     *
     * @param \Mod\Module $contentModule
     * @return \Mod\Menu\Crumbs
     */
    public function init($contentModule)
    {
        if ($this->getUri()->getPath(true) == $this->url->getPath(true)) {
            $this->reset();
        }
        if (!$this->current()) {
            $item = Item::create($this->text, $this->url, $this->icon);
            $this->addItem($item);
        }
        if ($this->current()->url->getPath(true) != $this->getUri()->getPath(true)) {
            $t = $contentModule->getTitle();
            if ($contentModule->getPage() && $contentModule->getPage()->getTitle()) {
                $t = $contentModule->getPage()->getTitle();
            }
            if ($t) {
                $item = Item::create($t, $this->getUri());
                $this->addItem($item);
            }
        }

        $this->getConfig()->set('mod.back.url', $this->getBackUrl());
        $this->locked = true;
        return $this;
    }

    /**
     * Is the crumb list locked.
     * If true no more crumbs can be added until the next page render
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }


    /**
     * Get the topmost url
     *
     * @return \Tk\Url
     */
    public function getBackUrl()
    {
        $backUrl = $this->children[0];
        foreach($this->getChildren() as $item) {
            if ($this->getUri()->toString() == $item->url->toString()) {
                return $backUrl;
            }
            $backUrl = $item->url;
        }
        return $backUrl;
    }

    /**
     * Push a url onto the end of the crumb stack
     * If url is null then the requestUri is used
     *
     * @param \Mod\Menu\Item $item
     * @return \Mod\Menu\Item
     */
    public function addItem($item)
    {
        if ($item->url->exists('_nc') || preg_match('|/ajax/|', $item->url->getPath()) || $this->isLocked()) {
            return;
        }
        // normalise url
        $strUrl = $item->url->getPath();
        if (!preg_match('/\.html$/', $strUrl)) {
            if (substr($strUrl, -1) != '/') {
                $strUrl .= '/';
            }
            $item->url->setPath($strUrl);
        }
        if ($this->exists($item->text)) {
            $this->trimByTitle($item->text);
        }
        $r = parent::addItem($item);
        return $r;
    }


    /**
     * trim the stack back to the requested Url
     *
     * @param \Tk\Url $url
     * @param bool $queryString
     * @return \Mod\Menu\Crumbs
     */
    protected function trimByUrl(\Tk\Url $url, $queryString = false)
    {
        $newArr = array();
        /* @var $item \Mod\Menu\Item */
        foreach ($this->children as $item) {
            if (!$queryString && $url->getPath(true) == $item->url->getPath(true)) {
                break;
            }
            if ($queryString && $url->toString() == $item->url->toString()) {
                break;
            }
            $newArr[] = $item;
        }
        $this->children = $newArr;
        return $this;
    }

    /**
     * trimName
     *
     * @param string $text
     * @return \Mod\Menu\Crumbs
     */
    protected function trimByTitle($text)
    {
        $newArr = array();
        /* @var $item \Mod\Menu\Item */
        foreach ($this->children as $item) {
            if ($item->text == $text) {
                break;
            }
            $newArr[] = $item;
        }
        $this->children = $newArr;
        return $this;
    }


    /**
     * Create a default name from the url
     *
     * @param \Tk\Url $url
     * @return string
     */
    protected function makeText(\Tk\Url $url)
    {
        $text = basename($url->getPath());
        $pos = strrpos($text, '.');
        if ($pos) {
            $text = substr($text, 0, $pos);
        }
        if ($text == 'index') {
            $text = basename(dirname($url->getPath()));
            if (!$text)
                $text = $this->text;
        }

        $text = trim(preg_replace('/[A-Z]/', ' $0', ucfirst($text)));

        return $text;
    }

    /**
     * Check if an item exits with the same text
     *
     * @param string $text
     * @return bool
     */
    public function exists($text)
    {
        return $this->findByText($text);
    }


    public function toString()
    {
        $str = '';
        /* @var $item Item */
        foreach($this->children as $item) {
            $str .= sprintf("%s - %s \n", $item->text, $item->url);
        }

        return $str;
    }

}