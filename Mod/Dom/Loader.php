<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom;

/**
 * A class to manage the loading of Dom_Template files.
 * This class give you the extendable option to locate
 * module DOMTemplate src files as needed
 *
 * @package Mod\Dom
 */
class Loader extends \Tk\Object
{
    /**
     * @var \Mod\Dom\Loader
     */
    static $instance = null;


    /**
     * @var string
     */
    protected $file = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $templateClass = '\Dom\Template';

    /**
     * @var string
     */
    protected $encoding = 'utf-8';


    /**
     * Sigleton, No instances can be created.
     *
     *
     */
    private function __construct()
    {

    }

    /**
     * Get an instance of this object
     *
     * @return \Mod\Dom\Loader
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Resets
     */
    private function reset()
    {
        $this->file = '';
        $this->class = '';
    }

    /**
     * Encoding
     *
     * @param string $enc
     */
    public function setEncoding($enc = 'utf-8')
    {
        $this->encoding = $enc;
    }

    /**
     * Set the found template filename
     *
     * @param string $file
     * @param bool $overwrite (Optional)
     */
    public function setFile($file, $overwrite = false)
    {
        if (!$this->file || $overwrite) {
            $this->file = $file;
        }
    }

    /**
     * Get the file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Test if template path found or not empty
     *
     * @return bool
     */
    public function hasFile()
    {
        if ($this->getFile()) {
            return true;
        }
        return false;
    }

    /**
     * Get the called class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the template class to use
     * Default: \Dom\Template
     *
     * @param string $class
     */
    public function setTemplateClass($class)
    {
        $this->templateClass = $class;
    }

    /**
     * Get the current template class name
     *
     * @return string
     */
    public function getTemplateClass()
    {
        return $this->templateClass;
    }



    /**
     * Load an xml strings
     *
     * @param string $defaultXml
     * @param string $class
     * @return \Dom\Template
     */
    static function load($defaultXml = '', $class = '')
    {
        return self::getInstance()->doLoad($defaultXml, $class);
    }

    /**
     * Load an xml file
     *
     * @param string $defaultFile
     * @param string $class
     * @return \Dom\Template
     */
    static function loadFile($defaultFile = '', $class = '')
    {
        return self::getInstance()->doLoadFile($defaultFile, $class);
    }



    /**
     * Load a template from a string
     *
     * @param string $defaultXml
     * @param string $class
     * @return \Dom\Template
     */
    public function doLoad($defaultXml = '', $class = '')
    {
        $this->class = $class;
        $template = null;

        // Run finders
        $this->notify();

        if ($this->file && is_file($this->file)){
            $template = call_user_func(array($this->templateClass, 'loadFile'), $this->file, $this->encoding);
        } else {
            $template = call_user_func(array($this->templateClass, 'load'), $defaultXml, $this->encoding);
        }
        $this->reset();
        return $template;
    }

    /**
     * load a template from a file
     *
     * @param string $defaultFile
     * @param string $class
     * @return \Dom\Template
     */
    public function doLoadFile($defaultFile = '', $class = '')
    {
        $this->class = $class;
        $template = null;

        // Run finders
        $this->notify();


        if ($this->file && is_file($this->file)){
            $template = call_user_func(array($this->templateClass, 'loadFile'), $this->file, $this->encoding);
        } else if (is_file($defaultFile)) {
            $template = call_user_func(array($this->templateClass, 'loadFile'), $defaultFile, $this->encoding);
        }
        $this->reset();
        return $template;
    }



}
