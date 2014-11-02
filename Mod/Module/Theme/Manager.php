<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Module\Theme;
use Mod\AdminPageInterface;

/**
 * Error module, for creating an error message when modules do not exist
 *
 * @package Mod\Module\Theme
 */
class Manager extends \Mod\Module
{
    /**
     * @var string
     */
    protected $msg = '';

    /**
     * Send an error mesage as a module
     *
     * @param string $msg
     */
    public function __construct($msg)
    {
        $this->msg = $msg;
        $this->set(AdminPageInterface::CRUMBS_RESET, true);
    }

    /**
     * Null module show nothing
     */
    public function init() {}
    public function execute() {}
    public function show()
    {
        $template = $this->getTemplate();
        $template->insertText('msg', $this->msg);
    }

    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0"?>
<div style="color: #FFF; background-color: #F99; border: 1px solid #F33;padding: 2px 5px;" var="msg"></div>
XML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }
}
