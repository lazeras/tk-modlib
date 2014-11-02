<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Menu;

/**
 *
 *
 * @package Mod\Menu
 */
class Divider extends Item
{



    /**
     * Create a new divider menu Item
     *
     * @param string $class (optional)
     * @return \Mod\Menu\Divider
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCssClass('divider');
    }




}
