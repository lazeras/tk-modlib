<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;

/**
 * Setup a default theme object for the page constructor
 *
 *
 * @package Mod\Controller
 */
class Theme extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName().'::update()');
        
        $dispatcher = $this->getConfig()->getDispatcher();
        $params = $dispatcher->getParams();
        
        if(!$this->getConfig()->get('system.theme.selected.themeFile')) {
            $themeFile = $this->getConfig()->get('system.theme.default.themeFile');
            if (!empty($params['themeFile'])) {
                $themeFile = $params['themeFile'];
            }
        } else {
            $themeFile = $this->getConfig()->get('system.theme.selected.themeFile');
        }
        
        $themeName = $this->getConfig()->get('system.theme.default.name');
        if ($this->getSession()->get(\Mod\Module\ThemeSelect::SID) != '') {
            $themeName = $this->getSession()->get(\Mod\Module\ThemeSelect::SID);
        }

        $themePath = str_replace($this->getConfig()->getSitePath(), '', $this->getConfig()->getThemePath());
        $theme = new \Mod\Theme($themePath, $themeName, $themeFile);
        
        $path = $this->getConfig()->getSitePath() . $theme->getThemeBasePath() . '/' . $theme->getThemeName();
        $url = $this->getConfig()->getSiteUrl() . $theme->getThemeBasePath() . '/' . $theme->getThemeName();

        $this->getConfig()->set('system.theme.selected', $theme);
        $this->getConfig()->set('system.theme.selected.name', $themeName);
        $this->getConfig()->set('system.theme.selected.path', $path);
        $this->getConfig()->set('system.theme.selected.url', $url);
        $this->getConfig()->set('system.theme.selected.themeFile', $themeFile);
    }


}