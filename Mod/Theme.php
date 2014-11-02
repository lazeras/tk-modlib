<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 * A theme data object. holds information reguarding the theme files
 * to use for the site.
 *
 *
 * @package Mod
 */
class Theme extends \Tk\Object
{
    const TPL_NONE = '__null__.tpl';
    const TPL_DEFAULT = 'public.tpl';

    /**
     * @var string
     */
    protected $themeFile = self::TPL_DEFAULT;

    /**
     * @var string
     */
    protected $themeName = 'default';

    /**
     * @var string
     */
    protected $themeBasePath = '/theme';




    /**
     * __construct
     *
     *
     * @param string $themeBasePath The Relative path to the themes folder from the site path
     * @param string $themeName
     * @param string $themeFile
     */
    public function __construct($themeBasePath = '/theme', $themeName = 'default', $themeFile = self::TPL_DEFAULT)
    {
        $this->themeBasePath = $themeBasePath;
        $this->themeName = $themeName;
        $this->themeFile = $themeFile;
    }

    /**
     * Set the filename that should be used for the page template
     * Default: 'main.html'
     *
     * @param string $file
     * @return \Mod\Page
     */
    public function setThemeFile($file)
    {
        if ($file[0] == '/') {
            $file = substr($file, 1);
        }
        $this->themeFile = $file;
        return $this;
    }

    /**
     * Get the base templakte file name
     *
     * @return string
     */
    public function getThemeFile()
    {
        return $this->themeFile;
    }

    /**
     * Set the theme name.
     * This is the folder inside the theme path
     * {sitePath}/{themePath}/{themeName}/main.html
     *
     * @param string $name
     * @return \Mod\Page
     */
    public function setThemeName($name)
    {
        $this->themeName = $name;
        return $this;
    }

    /**
     * Get the selected theme name
     *
     * @return string
     */
    public function getThemeName()
    {
        return $this->themeName;
    }

    /**
     * Set the theme base path, relative to the sitePath
     *
     * @param $path
     * @return \Mod\Page
     */
    public function setThemeBasePath($path)
    {
        $this->themeBasePath = $path;
        return $this;
    }

    /**
     * Get the theme base path relative to the sitePath
     *
     * @return string
     */
    public function getThemeBasePath()
    {
        return $this->themeBasePath;
    }

}
