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
class FormStatic extends \Mod\Module
{
    /**
     * @var \Form\Form
     */
    protected $form = null;



    public function __construct()
    {
        $this->setPageTitle('Forms Static Renderer Example');
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
        $this->form->attach($ff->createEventLink('cancel'))->setRedirectUrl(\Tk\Url::create('/admin/index.html'));


        $this->form->addField($ff->createFieldText('email'));
        $this->form->addField($ff->createFieldText('password'))->setRequired();
        $this->form->addField($ff->createFieldText('remember'));
        ;
        //$this->form->addField($ff->createFieldAutocombo('autocombo', ''))->setRequired();
        //$this->form->addField($ff->createFieldAutocomplete('autocomplete', ''))->setRequired();
//        $this->form->addField($ff->createFieldCheckboxGroup('checkboxGroup', $options))->addError('Invalid Field Data');
//        $this->form->addField($ff->createFieldDate('date'))->setNotes('Click to select a date.');
//        $this->form->addField($ff->createFieldDateTime('dateTime'));
//        $this->form->addField($ff->createFieldFile('file'));
//        $this->form->addField($ff->createFieldHidden('hidden'));
//        $this->form->addField($ff->createFieldMoney('money'))->addError('Invalid Field Data');
//        $this->form->addField($ff->createFieldPassword('password'))->addError('Invalid Field Data');
//        $this->form->addField($ff->createFieldRadioGroup('radioGroup', $options));
//        $this->form->addField($ff->createFieldSelect('select', $options));
//        $this->form->addField($ff->createFieldDualSelect('dualSelect', $options, $selected))->addError('Invalid Field Data');

        //$this->form->addField($ff->createFieldGmapSelect('mapSelect'))->addCssClass('span6');
        //$this->form->addField($ff->createFieldMce('tinyMce', \TinyMce\TinyMce::createFull('#fid-tinyMce')->addPlugin(new \TinyMce\Plugin\ElFinder())));
        //$this->form->addField($ff->createFieldTextarea('comments'))->addCssClass('span6')->addStyle('height', '150px');
        //$this->form->addField($ff->createFieldCaptcha('capcha'))->setRequired();

        $this->addChild($ff->createStaticFormRenderer($this->form, $this->getTemplate()));

        
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
  <form id="Form" class="form-horizontal" role="form">
    <input type="hidden" name="__submitId" />
    <div class="form-group">
      <label for="inputEmail1" class="control-label">Email</label>
      <div class="">
        <input type="email" class="form-control" id="inputEmail1" name="email" placeholder="Email" />
      </div>
    </div>
    <div class="form-group">
      <label for="inputPassword1" class="control-label">Password</label>
      <div class="">
        <input type="password" class="form-control" id="inputPassword1" name="password" placeholder="Password" />
      </div>
    </div>
    <div class="form-group">
      <div class="">
        <div class="checkbox">
          <label>
            <input type="checkbox" name="remember" /> Remember me
          </label>
        </div>
      </div>
    </div>
    <div class="form-group">
      <div class="">
        <input type="submit" class="btn btn-default" name="send" value="Send" />
        <input type="submit" class="btn btn-default" name="cancel" value="Cancel" />
      </div>
    </div>
  </form>

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
        if (!$form->getFieldValue('email')) {
            $form->addFieldError('email', 'Please add a valid email');
        }
        
        if ($form->hasErrors()) {
            return;
        }
        \Mod\Notice::addSuccess('Message Submited Successfully. Thank You!');
    }

}
