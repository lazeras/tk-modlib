<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Filter;

/**
 * Append all scripts to the bottoem of the body tag.
 * This is a current technique employed by designers
 * for mobile devices to load faster.
 *
 * @package Mod\DomModifier\Filter
 */
class JsLast extends Iface
{

    private $nodeList = array();

    private $notRun = true;

    /**
     * __construct
     *
     */
    public function __construct()
    {

    }



    /**
     * pre init the front controller
     *
     * @param \DOMDocument $doc
     */
    public function init($doc)
    {

    }


    /**
     * Call this method to travers a document
     *
     * @param \DOMElement $node
     */
    public function executeNode(\DOMElement $node)
    {
        if ($node->nodeName == 'script' && $this->domModifier->inHead()) {
            $this->nodeList[] = $node->cloneNode(true);
            $this->domModifier->removeNode($node);
        }

        if ($this->domModifier->getBody() && count($this->nodeList) && $this->notRun) {
            $this->notRun = false;
            foreach ($this->nodeList as $child) {
                $nl = $child->ownerDocument->createTextNode("\n");
                $this->domModifier->getBody()->appendChild($nl);
                $this->domModifier->getBody()->appendChild($child);
            }
        }

    }


}
