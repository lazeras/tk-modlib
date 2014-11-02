<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Module\Dev;
use Mod\AdminPageInterface;

/**
 * Typography example for admin templating...
 *
 * @package Mod\Module\Dev
 */
class Type extends \Mod\Module
{




    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Typography');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);

    }


    /**
     * init
     *
     */
    function init()
    {
    }


    /**
     * Show
     */
    function show()
    {
        $template = $this->getTemplate();




    }

}