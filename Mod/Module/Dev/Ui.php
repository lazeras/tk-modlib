<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Module\Dev;
use Mod\AdminPageInterface;

/**
 *
 *
 * @package Mod\Module\Dev
 */
class Ui extends \Mod\Module
{
    public $actionEnable = false;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->setPageTitle('Bootstrap UI');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);

    }


    /**
     * init
     */
    public function init()
    {

    }

    /**
     * doDefault
     */
    public function doDefault()
    {

    }

    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();





    }


}




