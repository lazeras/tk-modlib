<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;


/**
 * Write the module tree to the response object's buffer
 * ready for ouput. The response object is like the output buffer of the system.
 * has methods for handling HTTP output and heades etc...
 *
 *
 * @package Mod\Controller
 */
class WriteResponse extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');

        if (!$this->getConfig()->get('res.page') instanceof \Mod\Page) {
            return;
        }
        $response = $this->getConfig()->getResponse();
        // Parse the DOM template and get the HTML contents
        if ($this->getConfig()->exists('res.page')) {
            $page = $this->getConfig()->get('res.page');

            // Parse DOM Template to string
//            $html = $page->getTemplate()->toString();
//            $tdoc = new \DOMDocument();
//            $tdoc->loadHTML($html);
            //$tdoc = $page->getTemplate()->getDocument();


            // Execute Dom Modifier....
            $dm = $this->getConfig()->getDomModifier();
            $dm->execute($page->getTemplate()->getDocument());

            $html = $page->getTemplate()->toString();

            // Write Template Contents
            $response->write($html);
        }





        // Add Debug console to page
        if (!$this->getConfig()->exists('debug.disableConsole') && $this->getConfig()->isDebug()) {
            $dcon = new \Tk\Debug\Console($response->toString());
            $dcon->addExtra('Config', ' '.nl2br(htmlentities($this->getConfig()->toString())));
            $response->reset(true);
            $response->write($dcon->getHtml());
        }

        // Add HTML header and flush/output the response buffer
        $response->addHeader('Content-Type', 'text/html; charset=utf-8');
        $response->flush();

    }
}