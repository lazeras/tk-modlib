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
class FormTabs extends \Mod\Module
{
    /**
     * @var Form
     */
    protected $table = null;



    public function __construct()
    {
        $this->setPageTitle('Form Tabs Example');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);
    }


    /**
     * init
     *
     */
    function init()
    {
        $ff = \Form\Factory::getInstance();

        $this->form = $ff->createForm('Form');
        $this->form->attach(new Send('send'), 'save');
        $this->form->attach($ff->createEventLink('cancel'), 'cancel')->setRedirectUrl(\Tk\Url::create('/admin/index.html'));

        $options  = array( array('-- SELECT --', ''), array('Option 1', '1'), array('Option 2', '2'), array('Option 3', '3') );
        $selected = array( array('Option 1', '1'), array('Option 2', '2') );

        $this->form->addField($ff->createFieldHidden('hidden'));

        $this->form->addField($ff->createFieldText('title'))->setTabGroup('Tab 1')->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldText('email'))->setTabGroup('Tab 1')->setRequired()->setNotes('Please enter a valid email.');
        //$this->form->addField($ff->createFieldAutocombo('autocombo', \Tk\Url::create('/ajax/test.json')))->setTabGroup('Tab 1')->setRequired();
        //$this->form->addField($ff->createFieldAutocomplete('autocomplete', \Tk\Url::create('/ajax/test.json')))->setTabGroup('Tab 1')->setRequired();
        $this->form->addField($ff->createFieldCheckboxGroup('checkboxGroup', $options))->setTabGroup('Tab 1')->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldTextarea('comments'))->setTabGroup('Tab 1')->addCssClass('span6')->addStyle('height', '150px');
        $this->form->addField($ff->createFieldCaptcha('capcha'))->setTabGroup('Tab 1')->setRequired();

        $this->form->addField($ff->createFieldDate('date'))->setTabGroup('Tab 2')->setNotes('Click to select a date.');
        //$this->form->addField($ff->createFieldDateTime('dateTime'))->setTabGroup('Tab 2');
        $this->form->addField($ff->createFieldFile('file'))->setTabGroup('Tab 2');
        $this->form->addField($ff->createFieldMoney('money'))->setTabGroup('Tab 2')->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldPassword('password'))->setTabGroup('Tab 2')->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldRadioGroup('radioGroup', $options))->setTabGroup('Tab 2');
        $this->form->addField($ff->createFieldSelect('select', $options))->setTabGroup('Tab 2');
        $this->form->addField($ff->createFieldDualSelect('dualSelect', $options, $selected))->setTabGroup('Tab 2')->addError('Invalid Field Data');

        $this->form->addField($ff->createFieldGmapSelect('mapSelect'))->setTabGroup('Maps')->addCssClass('span6');

        //$this->form->addField($ff->createFieldMce('tinyMce', \TinyMce\TinyMce::createFull('#fid-tinyMce')->addPlugin(new \TinyMce\Plugin\ElFinder())))->setTabGroup('Edit');

        $this->addChild($ff->createFormRenderer($this->form), $this->form->getId());

    }

    /**
     * Show
     */
    function show()
    {
        $template = $this->getTemplate();




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
<div>
  <div var="Form"></div>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}


/**
 *
 *
 * @package Adm
 */
class Send extends \Form\Event\Button
{

    /**
     * execute
     *
     * @param Form $form
     */
    public function update($form)
    {

        if ($form->hasErrors()) {
            return;
        }

        \Mod\Notice::addSuccess('Message Submited Successfully. Thank You!');

    }

}
