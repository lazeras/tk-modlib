<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;

/**
 * Run the dispatcher and build the page,
 * The page is the root node of the module tree.
 * Insert modules in this tree to ensure they are executed correctly.
 *
 * It also checks the tree and config to see if the page should be in
 * SSL mode and redirects accordingly if required.
 *
 * @package Mod\Controller
 */
class Dispatch extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');
        \Dom\Template::$capture = $this->getConfig()->get('system.dom.capture');

        // Execute dispacher to locate a dynamic content Module
        $this->getConfig()->getDispatcher()->execute();
        
        // Set this to the requests required permission for the Auth module to kick in.
        $this->getConfig()->set('res.system.permission', 'public');

    }

}
