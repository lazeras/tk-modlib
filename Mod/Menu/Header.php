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
class Header extends Item
{



    /**
     * Create a new divider menu Item
     *
     * @param string $class (optional)
     * @return \Mod\Menu\Header
     */
    public function __construct($text)
    {
        parent::__construct($text);
        $this->setCssClass('dropdown-header');
    }




}
