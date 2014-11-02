<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;


/**
 * Execute the render method of the module tree
 * this in turn executed the show() mothod of all
 * modules in the tree.
 *
 *
 * @package Mod\Controller
 */
class Render extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');
        
        if ($this->getConfig()->exists('res.page')) {
            if (method_exists($this->getConfig()->get('res.page'), 'render')) {
                $this->getConfig()->get('res.page')->render();
            }
        }
    }

    
}