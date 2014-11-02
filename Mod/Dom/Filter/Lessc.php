<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Filter;

/**
 * Compile all CSS LESS code to CSS
 *
 * To Enable use composer.json to include LESS package.
 *
 * "leafo/lessphp": "0.4.*"
 *
 *
 * @package Mod\Dom\Filter
 * @deprecated use Mod\Dom\Filter\Less
 */
class Lessc extends Iface
{
    /**
     * @var \Tk\Cache\Cache
     */
    protected $cache = null;

    /**
     * @var \lessc
     */
    protected $less = null;

    /**
     * The number of hours to refresh the cache.
     * @var int
     */
    protected $hours = 6;

    /**
     * @var bool
     */
    protected $compress = true;



    /**
     * pre init the Filter
     *
     * @param \DOMDocument $doc
     * @throws Exception
     */
    public function init($doc)
    {
        tklog('Deprecated use Mod\Dom\Filter\Less');
        if (!class_exists('lessc')) {
            throw new Exception('Please install lessphp (http://leafo.net/lessphp/) into the vendor folder as `leafo/lessphp`');
        }
        $this->cache = $this->getConfig()->getCache();
        $this->less = new \lessc();

//        if ($this->getConfig()->isDebug()) {
//            $this->compress = false;
//        }

        $this->less->addImportDir($this->getConfig()->getSelectedThemeUrl());

        $this->less->registerFunction('makeTplUrl',   array(get_class($this), 'less_tplUrl'));   // Deprecated
        $this->less->registerFunction('makeThemeUrl', array(get_class($this), 'less_themeUrl'));
        $this->less->registerFunction('makeSiteUrl',  array(get_class($this), 'less_siteUrl'));
        $this->less->registerFunction('makeDataUrl',  array(get_class($this), 'less_dataUrl'));
        $this->less->registerFunction('makeLibUrl',   array(get_class($this), 'less_libUrl'));
        $this->less->registerFunction('makeMediaUrl', array(get_class($this), 'less_mediaUrl'));

        $const = array(
            'TPL_URL' => enquote($this->getConfig()->getSelectedThemeUrl()),
            'THEME_URL' => enquote($this->getConfig()->getSelectedThemeUrl()),
            'SITE_URL' => enquote($this->getConfig()->getSiteUrl()),
            'DATA_URL' => enquote($this->getConfig()->getDataUrl()),
            'LIB_URL' => enquote($this->getConfig()->getLibUrl()),
            'MEDIA_URL' => enquote($this->getConfig()->getMediaUrl())
        );
        $this->less->setVariables($const);

    }

    /**
     * Call this method to travers a document
     *
     * <code>
     * .logo {
     *   background-image: url(@TPL_URL);
     * }
     * .logo {
     *   background-image: url('@{SITE_URL}/images/test.png');
     * }
     * .logo {
     *   background--image: makeDataUrl('/images/test.png');
     * }
     * </code>
     *
     * @param \DOMElement|\Mod\Dom\Filter\DOMElement $node
     * @throws Exception
     */
    public function executeNode(\DOMElement $node)
    {
        if ($node->nodeName == 'link' && $node->hasAttribute('href') && preg_match('/\.less$/', $node->getAttribute('href'))) {
            $file = basename($node->getAttribute('href'));
            $filePath = str_replace($this->getConfig()->getSiteUrl(), '', dirname($node->getAttribute('href')));
            $filePath = $this->getConfig()->getSitePath() . $filePath .'/'.$file;

            if (!is_file($filePath)) {
                tklog('LESS File Not Found: ' . $filePath);
                return;
            }

            $key = md5($node->getAttribute('href') . $filePath);
            $out = $this->cache->fetch($key);

            tklog('LESS Compiling...Start');
            set_time_limit(120);
            if (!$out) {
                $out = $this->less->cachedCompile($filePath);
            } else {
                tklog('LESS Cache used.');
                $out = $this->less->cachedCompile($out);
            }
            $this->cache->store($key, $out, 60*60*$this->hours);
            $css = $out['compiled'];
            tklog('LESS Compiling...Finish');

            // Add new CSS to document
            $newNode = $node->ownerDocument->createElement('style');
            if ($node->hasAttribute('type')) {
                $newNode->setAttribute('type', 'text/css');
            }
            if ($this->compress) {
                $css = self::compressCss($css);
            }
            $ct = $node->ownerDocument->createCDATASection("\n" . $css . "\n" );
            $newNode->appendChild($ct);

            $node->parentNode->insertBefore($newNode, $node);
            $this->domModifier->removeNode($node);

        } else if ($node->nodeName == 'style' && $node->getAttribute('type') == 'text/less' ) {

            $key = md5($node->nodeValue);
            $out = $this->cache->fetch($key);
            tklog('LESS Compiling...Start');
            set_time_limit(120);
            if (!$out) {
                $out = $this->less->compile($node->nodeValue);
            }
            $this->cache->store($key, $out, 60*60*$this->hours);
            $css = $out;
            tklog('LESS Compiling...Finish');

            // Add new CSS to document
            $newNode = $node->ownerDocument->createElement('style');
            if ($node->hasAttribute('type')) {
                $newNode->setAttribute('type', 'text/css');
            }

            if ($this->compress) {
                $css = self::compressCss($css);
            }
            $ct = $node->ownerDocument->createCDATASection("\n" . $css . "\n" );
            $newNode->appendChild($ct);

            $node->parentNode->insertBefore($newNode, $node);
            $this->domModifier->removeNode($node);
        }
    }













    /**
     *
     *
     *
     *
     */
    static function compressCss($css)
    {
        //return preg_replace('/\s+/', ' ', $css);

        $buffer = $css; // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); // Remove whitespace
        $buffer = str_replace(': ', ':', $buffer);
        $buffer = str_replace(' :', ':', $buffer);
        $buffer = str_replace(' ;', ';', $buffer);
        $buffer = str_replace('; ', ';', $buffer);
        $buffer = str_replace('{ ', '{', $buffer);
        $buffer = str_replace(' {', '{', $buffer);
        $buffer = str_replace('} ', '}', $buffer);
        $buffer = str_replace(' }', '}', $buffer);
        $buffer = str_replace(' ,', ',', $buffer);
        $buffer = str_replace(', ', ',', $buffer);
        $buffer = str_replace(' .', ' .', $buffer);
        //$buffer = str_replace(array("\t"), '', $buffer);
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);
        $buffer = str_replace(array(' ', ' '), ' ', $buffer);
        return $buffer;
    }



    /**
     * 
     * @param type $arg
     * @return type
     * @deprecated
     */
    static function less_tplUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getSelectedThemeUrl() . $path));
    }


    static function less_themeUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getSelectedThemeUrl() . $path));
    }

    static function less_siteUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getSiteUrl() . $path));
    }

    static function less_dataUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getDataUrl() . $path));
    }

    static function less_libUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getLibUrl() . $path));
    }

    static function less_mediaUrl($arg)
    {
        list($type, $quote, $path) = $arg;
        $path = $path[0];
        if ($path[0] != '/')
            $path = '/' . $path;
        return array($type, $quote, array(\Tk\Config::getInstance()->getMediaUrl() . $path));
    }

}
