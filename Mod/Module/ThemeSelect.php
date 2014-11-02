<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Module;

/**
 *
 *
 * @package Mod\Module
 */
class ThemeSelect extends \Mod\Module
{

    const SID = '_#_theme';

    /**
     * @var \Form\Form
     */
    protected $form = '';



    /**
     * init
     */
    public function init()
    {
        $ff = \Form\Factory::getInstance();

        $themeName = 'default';
        if ($this->getSession()->exists(self::SID)) {
            $themeName = $this->getSession()->get(self::SID);
        }

        $this->form = $ff->createForm('_ThemeSelect');
        $this->form->addCssClass('themeSelect');
        $se = new Update('go', $this->getPage());
        $this->form->attach($se);

        $themePath = $this->getConfig()->getSitePath() . $this->getPage()->getTheme()->getThemeBasePath();
        $arr = scandir($themePath);
        $sorted = array();
        foreach ($arr as $val) {
            if ($val[0] == '.') continue;
            $sorted[] = array($val, $val);
            //$sorted[] = array($info->name . ' [' . $info->version . ']', $val);
        }

        $this->form->addField($ff->createFieldSelect('theme', $sorted)->prependOption('-- Theme --', '', false))->setValue($themeName)->setLabel('')->addCssClass('input-sm');
        $this->form->addCssClass('form-inline');


        $fren = $ff->createFormRenderer($this->form);
        $fren->getTemplate()->removeClass('form', 'tk-form');



        $this->addChild($fren, $this->form->getId());

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
        if ($this->getConfig()->isDebug()) {
            $template->setChoice('show');
        }

        $template->appendJsUrl(\Tk\Url::create('/assets/tek-js/util.js'));
        $js = <<<JS
jQuery(function($) {
  $('#_ThemeSelect_go').hide();
  $('#_ThemeSelect_theme').change(function () { submitForm(this.form, 'go'); });
});
JS;
        $template->appendJs($js);

    }


    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<div class="">
  <div var="ThemeSelect" title="Select A Theme"></div>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}




/**
 *
 *
 *
 * @package Ext
 */
class Update extends \Form\Event\Button
{

    /**
     * @var \Mod\Page
     */
    protected $page = null;

    /**
     *
     * @param string $name
     * @param \Mod\page $page
     */
    public function __construct($name, $page)
    {
        parent::__construct($name, '');
        $this->page = $page;
        $this->addCssClass('btn-sm');
    }

    /**
     * execute
     *
     * @param \Form $form
     */
    public function update($form)
    {
        $vals = $form->getValuesArray();

        if (!isset($vals['theme']) || !$vals['theme']) {
            return;
        }

        if (preg_match('/^(admin)$/', $vals['theme'])) {
            $form->addFieldError('theme', 'Invalid Theme Selected');
            return;
        }

        if ($vals['theme'] != $this->page->getTheme()->getThemeName()) {
            if ($vals['theme']) {
                $this->getSession()->set(ThemeSelect::SID, $vals['theme']);
            } else {
                $this->getSession()->delete(ThemeSelect::SID);
            }
            \Mod\Notice::addSuccess('Theme successfully changed to `'.$vals['theme'].'`');
        }



    }
}

