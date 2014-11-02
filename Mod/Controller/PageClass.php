<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;

/**
 * This is a basi PageClass controller.
 * Create your own to have your own page base classes

 * @package Mod\Controller
 */
class PageClass extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * Call after dispatcher is executer
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName().'::update()');

        // Discover page Template if not set yet
        if (!$this->getConfig()->exists('res.pageClass')) {
            $this->getConfig()->set('res.pageClass', '\Mod\Page');
        }

    }

}
