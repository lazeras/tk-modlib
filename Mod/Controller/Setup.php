<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;


/**
 * This executes teh setup/init methods of the module tree
 * starting with the Page module an iterating through.
 *
 *
 * @package Mod\Controller
 */
class Setup extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');
        
        if (method_exists($this->getConfig()->get('res.page'), 'setup')) {
            $this->getConfig()->get('res.page')->setup();
        }
    }
}


