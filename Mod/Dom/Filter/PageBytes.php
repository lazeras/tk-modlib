<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Filter;

/**
 * This class is meant to be an indicator of sizes not an exact measurement
 *
 * For example any styles loaded using @import or flies loaded by dynamic javascript
 * will not be calculated.
 *
 * Also no image sizes are calculated. This could be a feature to add in the future.
 *
 * @package Mod
 */
class PageBytes extends Iface
{

    private $cssTotal = 0;

    private $jsTotal = 0;

    private $htmlTotal = 0;

    private $checkedHash = array();


    /**
     * __construct
     *
     */
    public function __construct()
    {

    }


    function getCssBytes()
    {
        return $this->cssTotal;
    }

    function getJsBytes()
    {
        return $this->jsTotal;
    }

    function getHtmlBytes()
    {
        return $this->htmlTotal;
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
     * pre init the front controller
     *
     * @param \DOMDocument $doc
     */
    public function postInit($doc)
    {
        $str = $doc->saveXML();
        if ($str) {
            $this->htmlTotal = str2bytes($str);
        }


        $j = $this->getJsBytes();
        $c = $this->getCssBytes();
        $h = $this->getHtmlBytes();
        $t = $j + $c +$h;
        $log = \Tk\Config::getInstance()->getLog()->getLogger();
        $log->log('------------- Page Bandwidth -------------');
        $log->log('INFO', sprintf('JS:        %10s        %10s b', \Tk\Path::bytes2String($j), $j));
        $log->log('INFO',sprintf('CSS:       %10s        %10s b', \Tk\Path::bytes2String($c), $c));
        $log->log('INFO',sprintf('HTML:      %10s        %10s b', \Tk\Path::bytes2String($h), $h));
        $log->log('INFO',sprintf('TOTAL:     %10s        %10s b', \Tk\Path::bytes2String($t), $t));
        $log->log('INFO','------------------------------------------');

    }


    /**
     * Call this method to travers a document
     *
     * @param \DOMElement $node
     */
    public function executeNode(\DOMElement $node)
    {

        try {
            $str = '';
            if ( $node->nodeName == 'script') {
                if ($node->hasAttribute('src') && preg_match('/\.(js)/', $node->getAttribute('href'))) {
                    if (is_file($node->getAttribute('src'))) {
                        $str = @file_get_contents($node->getAttribute('src'));
                    }
                } else if (!$node->hasAttribute('src')) {
                    $str = $node->nodeValue;
                }
                $hash = md5($str);
                if ($str && !in_array($hash, $this->checkedHash)) {
                    $this->jsTotal += str2bytes($str);
                }
                $this->checkedHash[] = $hash;
                $str = null;
                return;
            }
            if ( $node->nodeName == 'style') {
                $str = $node->nodeValue;
                $hash = md5($str);
                if ($str && !in_array($hash, $this->checkedHash)) {
                    $this->cssTotal += str2bytes($str);
                }
                $this->checkedHash[] = $hash;
                $str = null;
                return;
            }
            if ( $node->nodeName == 'link' && $node->hasAttribute('href') && preg_match('/\.(css)/', $node->getAttribute('href'))) {
                if (is_file($node->getAttribute('href'))) {
                    $str = @file_get_contents($node->getAttribute('href'));
                }
                $hash = md5($str);
                if ($str && !in_array($hash, $this->checkedHash)) {
                    $this->cssTotal += str2bytes($str);
                }
                $this->checkedHash[] = $hash;
                $str = null;
                return;
            }
            $str = null;
        } catch (\Exception $e) {}
    }


}
