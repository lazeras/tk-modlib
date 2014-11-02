<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;


/**
 * Execute the module's events in the tree if no
 * event exists then the doDefault method is called.
 *
 *
 * @package Mod\Controller
 */
class Execute extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');

        if (method_exists($this->getConfig()->get('res.page'), 'execute')) {
            $this->getConfig()->get('res.page')->execute();
        }

    }
}