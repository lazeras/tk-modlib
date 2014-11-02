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
 * {
 *   "require": {
 *     "oyejorge/less.php": "~1.5"
 *   }
 * }
 *
 *
 */
class Less extends Iface
{
    /**
     * @var \Tk\Cache\Cache
     */
    protected $cache = null;

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
     * @var array
     */
    protected $source = array();

    private $insNode = null;




    /**
     * pre init the Filter
     *
     * @param \DOMDocument $doc
     * @throws Exception
     */
    public function init($doc)
    {
        if (!class_exists('Less_Parser')) {
            throw new Exception('Please install lessphp. (http://lessphp.gpeasy.com/)');
        }

        $this->source[] = <<<LESS
@_themeUrl : {$this->enquote($this->getConfig()->getSelectedThemeUrl())};
@_siteUrl  : {$this->enquote($this->getConfig()->getSiteUrl())};
@_dataUrl  : {$this->enquote($this->getConfig()->getDataUrl())};
@_libUrl   : {$this->enquote($this->getConfig()->getLibUrl())};
@_mediaUrl : {$this->enquote($this->getConfig()->getMediaUrl())};

LESS;

    }


    /**
     * pre init the Filter
     *
     * @param \DOMDocument $doc
     * @throws Exception
     */
    public function postTraverse($doc)
    {
        tklog('-> Start LESS Parser');
        $cachePath = $this->getConfig()->getCachePath();
        $options = array('cache_dir' => $cachePath, 'compress' => $this->compress);
        //$css_file_name = \Less_Cache::Get($this->source, $options, false);
        $css_file_name = \Less_Cache::Get($this->source, $options);
        $css = file_get_contents($cachePath . '/' . $css_file_name);
        tklog('-> End LESS Parser');


        $newNode = $doc->createElement('style');
        $newNode->setAttribute('type', 'text/css');
        $newNode->setAttribute('data-author', 'PHP_LESS_Compiler');
        $ct = $doc->createCDATASection("\n" . $css . "\n" );
        $newNode->appendChild($ct);
        if ($this->insNode) {
            $this->insNode->parentNode->insertBefore($newNode, $this->insNode);
        } else {
            $this->domModifier->getHead()->appendChild($newNode);
        }


    }

    /**
     * Surround a string by quotation marks. Single quote by default
     *
     * @param string $str
     * @param string $quote
     * @return string
     */
    protected function enquote($str, $quote = '"')
    {
        return $quote . $str . $quote;
    }


    /**
     * Call this method to traverse a document
     *
     * @param \DOMElement | \Mod\Dom\Filter\DOMElement $node
     * @throws Exception
     */
    public function executeNode(\DOMElement $node)
    {
        if ($node->nodeName == 'link' && $node->hasAttribute('href') && preg_match('/\.less$/', $node->getAttribute('href'))) {
            $file = basename($node->getAttribute('href'));
            $filePath = str_replace($this->getConfig()->getSiteUrl(), '', dirname($node->getAttribute('href')));
            $filePath = $this->getConfig()->getSitePath() . $filePath .'/'.$file;
            $fileUrl = dirname($node->getAttribute('href'));
            $fileUrl = '';

            $this->source[$filePath] = $fileUrl;
            $this->domModifier->removeNode($node);

            $this->insNode = $node;
        } else if ($node->nodeName == 'style' && $node->getAttribute('type') == 'text/less' ) {
            $this->source[] = $node->nodeValue;
            $this->domModifier->removeNode($node);

            $this->insNode = $node;
        }
    }

}
