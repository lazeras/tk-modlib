<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Filter;

/**
 * Convert all relative paths to full path url's
 *
 * @package Mod\Dom\Filter
 */
class Browser extends Iface
{

    /**
     * @var array
     */
    private $browser = null;

    /**
     * __construct
     *
     */
    public function __construct()
    {

    }

    /**
     * Get the browser info
     *
     * @return array
     */
    public function getBrowserInfo()
    {
        if (!$this->browser) {
            $this->browser = browserInfo();
        }
        return $this->browser;
    }

    /**
     * pre init the
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
     * @throws \Mod\Dom\Exception
     */
    public function executeNode(\DOMElement $node)
    {

        if ($node->hasAttribute('browser-show')) {
            $arr = $this->decode($node->getAttribute('browser-show'));
            if (count($arr) != 3) {
                throw new \Mod\Dom\Exception('Invalid browser-show attribute: ' . $node->getAttribute('browser-show'));
            }

            $bro = $this->getBrowserInfo();
            $ver = $bro['version'];

            if ($arr[0] != $bro['name']) {
                $this->domModifier->removeNode($node);
                return;
            } else {
                switch ($arr[1]) {
                    case 'lt':
                        if ($ver >= $arr[2]) return;
                        break;
                    case 'gt':
                        if ($ver <= $arr[2]) return;
                        break;
                    case 'lte':
                        if ($ver > $arr[2]) return;
                        break;
                    case 'gte':
                        if ($ver < $arr[2]) return;
                        break;
                    case 'ne':
                        if ($ver == $arr[2]) return;
                        break;
                    case 'e':
                        if ($ver != $arr[2]) return;
                        break;
                }
                $this->domModifier->removeNode($node);
                return;
            }
            $node->removeAttribute('browser-show');
            return;
        }


        if ($node->hasAttribute('browser-hide')) {
            $arr = $this->decode($node->getAttribute('browser-hide'));
            if (count($arr) != 3) {
                throw new \Mod\Dom\Exception('Invalid browser-hide attribute: ' . $node->getAttribute('browser-hide'));
            }
            $bro = $this->getBrowserInfo();
            $ver = $bro['version'];

            if ($arr[0] == $bro['name']) {
                switch ($arr[1]) {
                    case 'lt':
                        if ($ver < $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                    case 'gt':
                        if ($ver > $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                    case 'lte':
                        if ($ver <= $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                    case 'gte':
                        if ($ver >= $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                    case 'ne':
                        if ($ver != $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                    case 'e':
                        if ($ver == $arr[2]) {
                            $this->domModifier->removeNode($node);
                            return;
                        }
                        break;
                }
            }
            $node->removeAttribute('browser-hide');
            return;
        }
    }

    /**
     *
     *
     * @param string $str
     * @return array
     */
    public function decode($str)
    {
        preg_match('/(.+)\:(lt|gt|gte|lte|e|ne)([0-9\.]{1,10})/i', $str, $regs);
        array_shift($regs);
        return $regs;
    }

}