<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom;

/**
 * This class is designed to take a \DOMDocument, traverse it and pass each Element to
 * the child Dom iterator.
 *
 * The main aim of this object is to make final last minute alterations to the dom template
 * before rendering ensuring that we only traverse the DOM tree once on the final render stage.
 *
 * This can be extended by adding Mod\DomModifier\Filter\Iface objects.
 *
 * Mod\DomModifier\Filter\Iface objects are used to optimise final template changes
 * for extended modifier functionality, just add new Mod\DomModifier objects to the Tk\Controller\DomModifier object.
 *
 * See the Tk\Config::getDomModifier() to get a basic implementation of the DomModifier.
 *
 * Example:
 * <code>
 * <?php
 *
 *
 * ?>
 * </code>
 *
 *
 * @package Mod\Dom
 */
class Modifier extends \Tk\Object
{
    /**
     * @var array
     */
    protected $list = array();

    /**
     * @var array
     */
    protected $nodeTrash = array();

    /**
     * @var \DOMNode
     */
    protected $head = null;

    /**
     * @var \DOMNode
     */
    protected $body = null;

    /**
     * @var bool
     */
    protected $inHead = false;

    /**
     * @var bool
     */
    protected $inBody = false;




    /**
     * add
     *
     * @param \Mod\Dom\Filter\Iface $mod
     * @return \Mod\Dom\Filter\Iface
     */
    public function add(Filter\Iface $mod)
    {
        $mod->setDomModifier($this);
        $this->list[] = $mod;
        return $mod;
    }

    /**
     * Set the mod list
     *
     * @param array $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * Get mod list
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Check to see if we are traversing inside the head node of the document
     *
     * @return \DOMNode
     */
    public function getHead()
    {
        return $this->head;
    }

    public function inHead()
    {
        return $this->inHead;
    }


    /**
     * Check to see if we are traversing inside the head node of the document
     *
     * @return \DOMNode
     */
    public function getBody()
    {
        return $this->body;
    }

    public function inBody()
    {
        return $this->inBody;
    }


    /**
     * Use this method to delete nodes,
     * They will be added to a queue for removal
     * If you used the DOM remove the DOM tree traversing will get
     * screwed up.
     *
     *
     * @param \DOMNode $node
     */
    public function removeNode($node)
    {
        $this->nodeTrash[] = $node;
    }

    /**
     * Call this method to traverse a document
     *
     * @param \DOMDocument $doc
     * @return \DOMDocument
     */
    public function execute(\DOMDocument $doc)
    {
        $doc->normalizeDocument();
        foreach ($this->list as $mod) {
            tklog('Mod\Dom\Modifier::init() - ' . get_class($mod));
            $mod->init($doc);
        }
        $this->traverse($doc->documentElement);

        foreach ($this->list as $mod) {
            $mod->postInit($doc);   // << deprecated
            $mod->postTraverse($doc);
        }
        // Clear trash
        if (count($this->nodeTrash)) {
            foreach ($this->nodeTrash as $node) {
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
            gc_collect_cycles();
        }
        return $doc;
    }

    /**
     * Traverse a document converting element attributes.
     *
     * @param \DOMNode $node
     */
    private function traverse(\DOMNode $node)
    {
        if ($node->nodeType == \XML_ELEMENT_NODE) {
            if ($node->nodeName == 'head') {
                $this->head = $node;
                $this->inHead = true;
            }
            if ($node->nodeName == 'body') {
                $this->body = $node;
                $this->inBody = true;
            }
            /* @var $iterator \Mod\Dom\Filter\Iface */
            foreach ($this->list as $mod) {
                if (!$mod->isEnabled()) continue;
                $mod->executeNode($node);
            }
        }
        if ($node->nodeType == \XML_COMMENT_NODE) {
            /* @var $iterator \Mod\Dom\Filter\Iface */
            foreach ($this->list as $mod) {
                if (method_exists($mod, 'executeComment')) {
                    if (!$mod->isEnabled()) continue;
                    $mod->executeComment($node);
                }
            }
        }

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->traverse($child);
            }
        }
        if ($node->nodeType == \XML_ELEMENT_NODE) {
            if ($node->nodeName == 'head') {
                //$this->head = null;
                $this->inHead = false;
            }
            if ($node->nodeName == 'body') {
                //$this->body = null;
                $this->inBody = false;
            }
        }

    }




}