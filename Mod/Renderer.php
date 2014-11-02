<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 * For classes that render dom templates.
 *
 * This is a good base for all renderer objects that implement the Dom_Template
 * it can guide you to create templates that can be inserted into other template
 * objects.
 *
 * If the current template is null then
 * the magic method __makeTemplate() will be called to create an internal template.
 * This is a good way to create a default template. But be aware that this will
 * be a new template and will have to be inserted into its parent using the Dom_Template::insertTemplate()
 * method.
 *
 * @package Mod
 */
abstract class Renderer extends \Tk\Object implements \Dom\RendererInterface
{

    /**
     * @var \Dom\Template
     */
    protected $template = null;

    /**
     * @var \stdClass
     */
    private $data = null;



    /**
     * Test if an array key exists in the renderer data list
     *
     * @param string $name
     * @return bool
     * @deprecated
     */
    public function dataExists($name)
    {
        return property_exists($this->data, $name);
    }

    /**
     * Add an item to the renderer data list
     *
     * @param string $name
     * @param mixed $val
     * @deprecated
     */
    public function setData($name, $val)
    {
        $this->data->$name = $val;
    }

    /**
     * Get an element from the renderer data list
     *
     * @param string $name If not set then all the data array is returned
     * @return mixed
     * @deprecated
     */
    public function getData($name = null)
    {
        if ($name) {
            return $this->data->$name;
        }
        return $this->data;
    }

    /**
     * Test if there is any data in teh data object.
     *
     * @return type
     * @deprecated
     */
    public function hasData()
    {
        return count(get_class_vars($this->data)) > 0;
    }


    /**
     * Set a new template for this renderer.
     *
     * @param \Dom\Template $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        $this->notify('setTemplate');
        return $this;
    }

    /**
     * Get the template
     * This method will try to call the magic method __makeTemplate
     * to get a template if non exsits.
     * Use this for objects that use internal templates.
     *
     * @return \Dom\Template
     */
    public function getTemplate()
    {
        if ($this->template) {
            return $this->template;
        }
        $magic = '__makeTemplate';
        if (method_exists($this, $magic)) {
            $this->setTemplate($this->$magic());
        }
        return $this->template;
    }


    /**
     * Test to see if this object has a template available
     *
     * @return bool
     */
    public function hasTemplate()
    {
        if ($this->getTemplate())
            return true;
        return false;
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

}