<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Module\Dev;use Mod\AdminPageInterface;

/**
 *
 *
 * @package Mod\Module\Dev
 */
class Index extends \Mod\Module
{
    public $actionEnable = false;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->setPageTitle('Development Admin Markup');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);
        $this->set(AdminPageInterface::PANEL_ACTIONS_ENABLE, false);

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

        if (class_exists('/Plg/Factory')) {
            $template->setChoice('plugins');
        }



    }

}




