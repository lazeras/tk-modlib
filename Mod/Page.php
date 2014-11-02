<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 * Module are the controller and view for the model or data
 * It is done this way for code effecientcy and the ability
 * to create a Module execution treenull
 *
 *
 * @package Mod
 */
class Page extends Module
{
    // Decided to keep the below constants even if not used in HTML markup
    const VAR_PAGE_TOP          = '_pageTop_';
    const VAR_PAGE_HEADER       = '_header_';
    const VAR_PAGE_CRUMBS       = '_crumbs_';
    const VAR_PAGE_CONTENT_HEAD = '_contentHead_';
    const VAR_PAGE_CONTENT      = '_content_';
    const VAR_PAGE_CONTENT_FOOT = '_contentFoot_';
    const VAR_PAGE_SIDE_NAV_1   = '_sideNav1_';
    const VAR_PAGE_SIDE_NAV_2   = '_sideNav2_';
    const VAR_PAGE_TOP_NAV_1    = '_topNav1_';
    const VAR_PAGE_FOOTER       = '_footer_';
    const VAR_PAGE_BOTTOM       = '_pageBottom_';
    
    const VAR_PAGE_TITLE        = '_pageTitle_';
    const VAR_SITE_TITLE        = '_siteTitle_';
    const VAR_SITE_VERSION      = '_siteVersion_';
    const VAR_URL_HOME          = '_homeUrl_';
    const VAR_URL_USERNAME      = '_username_';


    /**
     * @var \Mod\Theme
     */
    protected $theme = null;

    /**
     * @var array
     * @deprecated
     */
    protected $menuList = array();



    /**
     * __construct
     *
     * @param \Mod\Theme $theme
     */
    public function __construct(\Mod\Theme $theme = null)
    {
        $this->setTheme($theme);
        $this->setPage($this);
    }



    public function setup()
    {
        $r = parent::setup();
        /* @var $menu \Mod\Menu\Menu */
        foreach ($this->menuList as $menu) {
            if ($menu->count() || $menu->showEmpty) {
                $this->addChild($menu->getRenderer(), $menu->getRenderVar());
            }
        }
        return $r;
    }





    /**
     * Add a menu to the menu array
     * Please make the name the same var string in the template
     *
     * @param \Mod\Menu\Menu $mu
     * @return \Mod\Page
     * @deprecated
     */
    public function setMenu($mu)
    {
        $this->menuList[$mu->text] = $mu;
        return $this;
    }

    /**
     * Retrive a menu object from the page
     *
     * @param string $title
     * @return \Mod\Menu\Menu
     */
    public function getMenu($title)
    {
        return $this->menuList[$title];
    }

    /**
     * Add a link to a menu,
     * This is more of a help function
     * For more complex access use getMenu(...)->add(..);
     *
     * @param string $menuTitle The menu title to add the link to
     * @param \Mod\Menu\Item $item
     * @return \Mod\Page
     * @deprecated
     */
    public function addMenuItem($menuTitle, $item)
    {
        $menu = $this->getMenu($menuTitle);
        $menu->addItem($item);
        return $this;
    }


    /**
     * Get the main content module
     *
     * @return \Mod\Module
     */
    public function getContentChild()
    {
        return $this->getConfig()->get('res.pageContentModule');
    }




    /**
     * Set the theme object
     *
     * @param \Mod\Theme $theme
     * @return \Mod\Page
     */
    public function setTheme(\Mod\Theme $theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get the selected theme
     *
     * @return \Mod\Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Get the full theme path and filename
     *
     * @return string
     */
    public function getThemePath()
    {
        if ($this->theme) {
            return $this->getConfig()->getSitePath() . $this->theme->getThemeBasePath() . '/' . $this->theme->getThemeName() . '/' .
                $this->theme->getThemeFile();
        }
        return '';
    }

    /**
     * Get the theme URL path and filename
     *
     * @return string
     */
    public function getThemeUrl()
    {
        if ($this->theme) {
            return $this->theme->getThemeBasePath() . '/' . $this->theme->getThemeName() . '/' .
                $this->theme->getThemeFile();
        }
        return '';
    }

    /**
     * Return the rendered template as a string
     *
     * @return string
     */
    public function toString()
    {
        if ($this->getTemplate()) {
            return $this->getTemplate()->toString();
        }
        return '';
    }


    /**
     * Create a default template
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        if ($this->theme && is_file($this->getThemePath())) {
            return Dom\Loader::loadFile($this->getThemePath(), $this->getClassName());
        }
        // Default MINIMAL MARKUP
        $html = <<<HTML
<html>
  <head>
    <title var="_pageTitle_">PAGE</title>
  </head>
  <body>
    <div var="_content_"></div>
  </body>
</html>
HTML;
        return Dom\Loader::load($html, $this->getClassName());
    }

}
