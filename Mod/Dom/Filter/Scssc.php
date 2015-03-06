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
 * To Enable use composer.json to include SCSS package.
 *
 *  "leafo/scssphp": "0.0.9"
 *
 *
 * @package Mod\Dom\Filter
 * @todo Implement the SCSS from lefo
 */
class Scssc extends Iface
{

    /**
     * @var string
     */
    protected $tplUrl = '';


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
        throw new \Exception('Not implemented yet...');
        if (is_dir($this->getConfig()->getLibPath().'/PHamlP')) {
            //include($this->getConfig()->getLibPath().'/PHamlP/sass/SassParser.php');

            $tplPath = dirname($doc->documentURI);
            $this->tplUrl = str_replace($this->getConfig()->getSitePath(), '', $tplPath);
            $this->tplUrl = $this->getConfig()->getSiteUrl() . $this->tplUrl;
        }
    }


    /**
     * Call this method to travers a document
     *
     * @param \DOMElement $node
     */
    public function executeNode(\DOMElement $node)
    {
        if ( $node->nodeName == 'link' && $node->hasAttribute('href') && preg_match('/\.(scss)/', $node->getAttribute('href')) ) {
            if (!class_exists('PHamlP\sass\SassParser')) {
                throw new Exception('Install PHamlP (http://phamlp.googlecode.com/) into the lib folder as `PHamlP`');
            }
            $cache = $this->getConfig()->getCache();
            $key = $node->getAttribute('href');
            $out = $cache->fetch($key);

            if (!$out) {
                $file = basename($node->getAttribute('href'));
                $hrefUrl = dirname($node->getAttribute('href'));
                $hrefPath = str_replace(\Tk\Config::getInstance()->getSiteUrl(), \Tk\Config::getInstance()->getSitePath(), $hrefUrl);

                if (!is_file($hrefPath.'/'.$file)) {
                    \Tk\Config::getInstance()->getLog()->getLogger()->log('SCSS/SASS File Not Found: ' . $hrefPath.'/'.$file);
                    return;
                }

                $siteUrl = \Tk\Config::getInstance()->getSiteUrl();
                $dataUrl = \Tk\Config::getInstance()->getDataUrl();
                $libUrl = \Tk\Config::getInstance()->getLibUrl();

                $prependStr = <<<OUT
/********** Custom Methods **********/
\$TPL_URL:  '{$this->tplUrl}';
\$SITE_URL: '$siteUrl';
\$DATA_URL: '$dataUrl';
\$LIB_URL: '$libUrl';

@function makeTplUrl(\$src) {
@return url('{$this->tplUrl}#{\$src}');
}
@function makeSiteUrl(\$src) {
@return url('{$siteUrl}#{\$src}');
}
@function makeDataUrl(\$src) {
@return url('{$dataUrl}#{\$src}');
}
@function makeLibUrl(\$src) {
@return url('{$libUrl}#{\$src}');
}
/************************************/
OUT;
                /**
                * style:
                * @var string the style of the CSS output.
                * Value can be:
                * + nested - Nested is the default Sass style, because it reflects the
                * structure of the document in much the same way Sass does. Each selector
                * and rule has its own line with indentation is based on how deeply the rule
                * is nested. Nested style is very useful when looking at large CSS files as
                * it allows you to very easily grasp the structure of the file without
                * actually reading anything.
                * + expanded - Expanded is the typical human-made CSS style, with each selector
                * and property taking up one line. Selectors are not indented; properties are
                * indented within the rules.
                * + compact - Each CSS rule takes up only one line, with every property defined
                * on that line. Nested rules are placed with each other while groups of rules
                * are separated by a blank line.
                * + compressed - Compressed has no whitespace except that necessary to separate
                * selectors and properties. It's not meant to be human-readable.
                *
                * Defaults to 'nested'.
                */
                $parser = new PHamlP\sass\SassParser( array('style'=>'compact', 'cache' => false, 'prependStr' => $prependStr) );
                $out = $parser->toCss($hrefPath.'/'.$file);

                // Storing the data in the cache for 6 hours
                $cache->store($key, $out, 60*60*6);
            }

            $newNode = $node->ownerDocument->createElement('style');
            $newNode->setAttribute('type', 'text/css');
            $ct = $node->ownerDocument->createCDATASection("\n" . $out . "\n" );
            $newNode->appendChild($ct);

            $node->parentNode->insertBefore($newNode, $node);
            $this->domModifier->removeNode($node);


        }


    }



}
