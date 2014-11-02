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
class Form extends \Mod\Module
{
    /**
     * @var \Table\Table
     */
    protected $table = null;



    public function __construct()
    {
        $this->setPageTitle('Forms Example');
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
        $this->form->attach(new Send('send'));
        $this->form->attach($ff->createEventLink('cancel'), 'cancel')->setRedirectUrl(\Tk\Url::create('/admin/index.html'));

        // For select fields
        $options  = array( array('Option 1', '1'), array('Option 2', '2'), array('Option 3', '3') );
        $selected = array( '1', '2' );





        $this->form->addField($ff->createFieldFile('file'))->setRequired();
        $this->form->addField($ff->createFieldFileMultiple('multipleFiles'));




        // Add Fields
        $this->form->addField($ff->createFieldHidden('hidden'));

        $this->form->addField($ff->createFieldText('title'))->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldText('email'))->setRequired()->setNotes('Please enter a valid email.');
        //$this->form->addField($ff->createFieldAutocombo('autocombo', ''))->setRequired();
        //$this->form->addField($ff->createFieldAutocomplete('autocomplete', ''))->setRequired();
        $this->form->addField($ff->createFieldCheckboxGroup('checkboxGroup', $options))->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldDate('date'))->setNotes('Click to select a date.');
        $this->form->addField($ff->createFieldDateTime('dateTime'));

        $this->form->addField($ff->createFieldMoney('money'))->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldPassword('password'))->addError('Invalid Field Data');
        $this->form->addField($ff->createFieldRadioGroup('radioGroup', $options));
        $this->form->addField($ff->createFieldSelect('select', $options)->prependOption('-- Select --', ''));
        $this->form->addField($ff->createFieldSelectMulti('selectMulti', $options));
        $this->form->addField($ff->createFieldDualSelect('dualSelect', $options)->setValue($selected))->addError('Invalid Field Data');



        $this->form->addField($ff->createFieldGmapSelect('mapSelect'));


        // TODO: relocate the js source files to assets
        //$this->form->addField($ff->createFieldMce('tinyMce', \TinyMce\TinyMce::createFull('#fid-tinyMce')->addPlugin(new \TinyMce\Plugin\ElFinder())));
        //$this->form->addField($ff->createFieldTextarea('comments'));
        //$this->form->addField($ff->createFieldCaptcha('capcha'))->setRequired();

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
 * @package Adm\Module\Dev
 */
class Send extends \Form\Event\Button
{

    /**
     * execute
     *
     * @param \Form\Form $form
     */
    public function update($form)
    {
        $fileField = $form->getField('file');
        if ($fileField->getError()) {
            $fileField->addError($fileField->getFileName() . ': ' . $fileField->getErrorString());
        }

        /* @var $fileField \Form\Field\FileMultiple */
        foreach($form->getField('multipleFiles') as $fileField) {
            if (!$fileField->hasFile()) continue;
            if ($fileField->getError()) {
                $fileField->addError($fileField->getFileName() . ': ' . $fileField->getErrorString());
            }
        }


        if ($form->hasErrors()) {
            return;
        }



        \Mod\Notice::addSuccess('Message Submitted Successfully. Thank You!');
    }

}
