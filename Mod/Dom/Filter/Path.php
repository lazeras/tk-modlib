<?php
/*
 * This file is part of the DkLib.
 *   You can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   If not, see <http://www.gnu.org/licenses/>.
 *
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
class Path extends Iface
{

    /**
     * @var array
     */
    protected $attrSrc = array(
        // Custom data attributes
        'src', 'href', 'action', 'background',
        // Custom data attributes
        'data-href', 'data-src', 'data-url'
    );

    /**
     * @var array
     */
    protected $attrJs = array('onmouseover', 'onmouseup', 'onmousedown', 'onmousemove', 'onmouseover', 'onclick');

    /**
     * The site root file path
     * @var string
     */
    protected $siteUrl = '';

    /**
     * The site root file path
     * @var string
     */
    protected $themeUrl = '';



    /**
     * __construct
     *
     * @param string $siteUrl
     * @param string $themeUrl
     */
    public function __construct($siteUrl = '', $themeUrl = '')
    {
        $this->siteUrl = $siteUrl;
        $this->themeUrl = $themeUrl;
    }


    /**
     * Prepend the site document root url to the provided path
     *
     * Eg:
     *      $path = /path/to/resource.js
     *      return /site/root/path/to/resource.js
     *
     * @param string $path
     * @return string
     */
    protected function addSiteUrl($path)
    {
        //$path = str_replace($this->getConfig()->getSiteUrl(), '', $path);
        $path = str_replace($this->siteUrl, '', $path);
        $retPath = $this->siteUrl . $path;
        //$retPath = $this->getConfig()->getSiteUrl() . $path;
        //$retPath = str_replace(array('//','\\\\'), array('/','\\'), $retPath);
        return $retPath;
    }

    /**
     * Prepent the theme document root to the provided path
     *
     * Eg:
     *      $path = path/to/resource.js
     *      $path = ./path/to/resource.js
     *      $path = ../path/to/resource.js
     *      $path = ../../path/to/resource.js
     *      return /site/root/theme/selected/path/to/resource.js
     *
     * @param string $path
     * @return string
     */
    protected function addThemeUrl($path)
    {
        //$path = str_replace($this->getConfig()->getSelectedThemeUrl(), '', $path);
        $path = str_replace($this->themeUrl, '', $path);
        //$retPath =  $this->getConfig()->getSelectedThemeUrl() . '/' . $path;
        $retPath =  $this->themeUrl . '/' . $path;
        //$retPath = str_replace(array('//','\\\\'), array('/','\\'), $retPath);
        return $retPath;
    }

    /**
     * replace a string with paths using string replace.
     * Usefull for urls in script text and comments.
     *
     * @param $str
     * @return mixed
     */
    protected function replaceStr($str)
    {
        $str = str_replace('{sitePath}', $this->siteUrl, $str);
        $str = str_replace('{siteUrl}', $this->siteUrl, $str);
        $str = str_replace('{themePath}', $this->themeUrl, $str);
        $str = str_replace('{themeUrl}', $this->themeUrl, $str);

//        $str = str_replace('{sitePath}', $this->getConfig()->getSiteUrl(), $str);
//        $str = str_replace('{siteUrl}', $this->getConfig()->getSiteUrl(), $str);
//        $str = str_replace('{themePath}', $this->getConfig()->getSelectedThemeUrl(), $str);
//        $str = str_replace('{themeUrl}', $this->getConfig()->getSelectedThemeUrl(), $str);

        return $str;
    }

    /**
     * Clean a path from ./ ../ but keep path integrity.
     * eg:
     *
     *   From: /Work/Projects/tk003-trunk/theme/default/../../../../relative/path/from/theme.html
     *     To: /Work/relative/path/from/theme.html
     *
     * Note: This function can give access to unwanted paths if not used carfully.
     *
     * @param string $path
     * @return string
     */
    private function cleanRelative($path)
    {
        // TODO: could cause security issues. see how we go without it.
        $path = str_replace(array('//','\\\\'), array('/','\\'), $path);
        $array = explode( '/', $path);
        $parents = array();
        foreach( $array as $dir) {
            switch( $dir) {
                case '.':
                    // Don't need to do anything here
                    break;
                case '..':
                    array_pop( $parents);
                    break;
                default:
                    $parents[] = $dir;
                    break;
            }
        }
        return implode( '/', $parents);
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
     * @param \DOMComment $node
     */
    public function executeComment(\DOMComment $node)
    {
        $node->data = $this->replaceStr($node->data);
    }

    /**
     * Call this method to travers a document
     *
     * @param \DOMElement $node
     */
    public function executeNode(\DOMElement $node)
    {
        // Modify local paths to full path url's
        foreach ($node->attributes as $attr) {
            if (in_array(strtolower($attr->nodeName), $this->attrSrc)) {
                if (preg_match('/^#$/', $attr->value)) { // ignore hash urls
                    $attr->value = 'javascript:;';
                    continue;
                }
                if (preg_match('/^#/', $attr->value)) { // ignore fragment urls
                    continue;
                }
                if (preg_match('/(\S+):(\S+)/', $attr->value) || preg_match('/^\/\//', $attr->value)) {   // ignore full urls and schema-less urls
                    continue;
                }
                $attr->value = htmlentities($this->prependPath($attr->value));
            } elseif (in_array(strtolower($attr->nodeName), $this->attrJs)) {       // replace javascript strings
                $attr->value = htmlentities($this->replaceStr($attr->value));
            }
        }
    }

    /**
     * Prepend the path to a relative link on the page
     *
     *
     * @param string $path
     * @return string
     */
    private function prependPath($path)
    {
        if ($path[0] == '/' || $path[0] == '\\') {   // match site relative paths
            $retPath = $this->addSiteUrl($path);
        } else  {
            $retPath = $this->addThemeUrl($path);
        }
        return $retPath;
    }

}