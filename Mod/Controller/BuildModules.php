<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;



/**
 * The page is the root node of the module tree.
 * Insert modules in this tree to ensure they are executed correctly.
 *
 * @package Mod
 */
class BuildModules extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * Call after dispatcher is executed
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog('' . $this->getClassName() . '::update()');

        // Check for cancel build flag..
        if ($this->getConfig()->exists('res.page') || $this->getConfig()->exists('res.cancelBuildModules')) {
            return;
        }
        $dispatcher = $this->getConfig()->getDispatcher();

        /* @var $page \Mod\Page */
        $page = $this->getConfig()->createPage($this->getConfig()->get('res.pageClass'), $dispatcher->getParams());
        
        
        // init content module
        $contentClass = $dispatcher->getClass();
        $contentModule = null;

        if ($contentClass) {
            $contentModule = new $contentClass();
            $this->getConfig()->set('res.pageContentModule', $contentModule);
            foreach ($dispatcher->getParams() as $key => $value) {
                $method = 'set'.ucfirst($key);
                if (method_exists($contentModule, $method)) {
                    $contentModule->$method($value);
                } else {
                    $contentModule->set($key, $value);
                }
            }
        } else {
            $obs->showNotFoundError($this->getRequest()->getRequestUri());
        }
        $page->addChild($contentModule, \Mod\Page::VAR_PAGE_CONTENT);

        // TODO CONFIRM: This should not be here?
        //$this->getConfig()->set('res.system.permission', $page->getPermission());

        
    }


}
