<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Finder;

/**
 * This observer looks in the selected theme folder for Module xml templates
 *
 * @package Mod\Dom\Finder
 */
class Theme extends \Tk\Object implements Iface
{
    /**
     * @var \Mod\Page
     */
    protected $page = null;

    /**
     * Constructor
     *
     * @param \Mod\Page $page
     */
    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * Use this method to create and return the module class name
     * First checks the acive theme for any templates in the xml/ folder
     * if not them checks the default theme for any templates in the xml/ folder
     *
     * TODO: May have class clashes here, may need to add a subfolder for path. 05/2014 Let's see First!
     *
     * @param \Mod\Dom\Loader $obs
     */
    public function update($obs)
    {
        if ($obs->hasFile() || !$this->page->getTheme() || !$obs->getClass()) {
            return;
        }

        $file = '/' . self::fromNamespace($obs->getClass()) . '.xml';
        $cpath = str_replace($this->getConfig()->getSitePath(), '', $file);

        $path = dirname($this->page->getThemePath()) . $this->getConfig()->get('system.theme.default.xmlPath') . $cpath;
        $path = str_replace(array('..', './', '../'), '', $path);
        if (is_file($path)) {
            $obs->setFile($path);
            return;
        }
        // Fall back to the default theme for any templates
        $path = $this->getConfig()->get('system.theme.path') . '/' . $this->getConfig()->get('system.theme.default.name') .
            $this->getConfig()->get('system.theme.default.xmlPath') . $cpath;
        $path = str_replace(array('..', './', '../'), '', $path);
        if (is_file($path)) {
            $obs->setFile($path);
            return;
        }
    }


}



