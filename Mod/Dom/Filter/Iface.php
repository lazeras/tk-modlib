<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Filter;

/**
 * The interface for all DomModifier objects
 *
 *
 * @package Mod\Dom\Filter
 */
abstract class Iface extends \Tk\Object
{

    /**
     * @var \Mod\Dom\Modifier
     */
    protected $domModifier = null;

    /**
     * @var bool
     */
    protected $enabled = true;



    /**
     * Set Dom Modifier
     *
     * @param \Mod\Dom\Modifier $dm
     */
    public function setDomModifier(\Mod\Dom\Modifier $dm)
    {
        $this->domModifier = $dm;
    }

    /**
     * Set the enabled state of the object
     *
     * @param bool $b
     */
    public function setEnable($b)
    {
        $this->enabled = $b;
    }

    /**
     * Get the enabled status.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }


    /**
     * pre init the front controller
     *
     * @param \DOMDocument $doc
     */
    abstract function init($doc);


    /**
     * pre init the front controller
     *
     * @param \DOMDocument $doc
     * @deprecated Use postTraverse()
     */
    public function postInit($doc) { }

    /**
     * called after DOM tree is traversed
     *
     * @param \DOMDocument $doc
     */
    public function postTraverse($doc) { }


    /**
     * The code to perform any modification to the node goes here.
     *
     * @param \DOMElement $node
     */
    abstract function executeNode(\DOMElement $node);

}