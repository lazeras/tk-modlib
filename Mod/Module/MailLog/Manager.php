<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */
namespace Mod\Module\MailLog;

/**
 * Manager: Mail_Module_MailLog_Manager
 *
 * Manage records in the `mailLog` SQL table.
 *
 * Use the following markup to call this module in the template:
 *   <module class="Mod_Module_MailLog_Manager"></module>
 *
 *
 */
class Manager extends \Mod\Module
{
    /**
     * @var \Table\Table
     */
    public $table = null;


    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Log Manager');

        //

    }

    /**
     * init...
     *
     */
    public function init()
    {
        // Create Table structure
        $ff = \Form\Factory::getInstance();
        $tf = \Table\Factory::getInstance();

        $this->table = $tf->createTable('Manager');
        $this->table->addCell($tf->createCellCheckbox());
        $this->table->addCell(Subject::create('subject'))->setKey()->setUrl($this->getEditUrl());
        $this->table->addCell($tf->createCellString('to'));
        $this->table->addCell($tf->createCellBoolean('sent'));
        $this->table->addCell($tf->createCellDate('created')->setFormat(\Tk\Date::SHORT_DATETIME));

        $this->table->addAction(Send::create());
        if ($this->hasPermission()) {
            $this->table->addAction($tf->createActionDelete());
        }

        $this->table->addFilter($ff->createFieldText('keywords'));
        $this->table->addFilter($ff->createFieldDate('dateFrom'));
        $this->table->addFilter($ff->createFieldDate('dateTo'));

        $this->addChild($tf->createTableRenderer($this->table), $this->table->getId());

    }



    protected function getEditUrl()
    {
        return \Tk\Url::createHomeUrl('/mail/log/view.html');
    }

    protected function hasPermission()
    {
        return $this->getConfig()->getUser()->hasPermission(\Tk\Auth\Auth::P_ADMIN);
    }

    protected function getList()
    {
        $filter = $this->table->getFilterValues();
        return \Mod\Db\MailLog::getMapper()->findFiltered($filter, $this->table->getDbTool('`created` DESC'));
    }





    /**
     * default event
     *
     */
    public function doDefault()
    {
        $list = $this->getList();
        $this->table->setList($list);
    }

    /**
     * Render the module
     *
     */
    public function show()
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
  <div var="Manager"></div>
</div>
HTML;
        return \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
    }

}


class Subject extends \Table\Cell\Iface
{
    static function create($property, $name = '')
    {
        $obj = new self($property, $name);
        return $obj;
    }
    public function getPropertyValue($obj)
    {
        $max = 120;
        $value = parent::getPropertyValue($obj);
        if (strlen($value) > $max) {
            $value = wordcat($value,$max, '...');
        }
        return $value;
    }
}

class Send extends \Table\Action\Iface
{

    protected $confirmMsg = 'Are you sure you want to re-send the selected emails.';

    /**
     * Create a delete action
     *
     * @return \Mod\Module\MailLog\Manager
     */
    static function create()
    {
        $obj = new self('resend', \Tk\Config::getInstance()->getUri(), 'icon-envelope');
        $obj->setLabel('Send Selected');
        return $obj;
    }

    /**
     * setConfirm
     *
     * @param string $str
     * @return \Mod\Module\MailLog\Manager
     */
    public function setConfirm($str)
    {
        $this->confirmMsg = $str;
        return $this;
    }



    /**
     * (non-PHPdoc)
     * @see \Table\Action::execute()
     */
    public function execute($list)
    {
        $selected = $this->getRequest()->get($this->getObjectKey(\Table\Cell\Checkbox::CB_NAME));
        if (count($selected)) {
            $i = 0;
            $s = 0;
            foreach ($list as $obj) {
                if (!$obj instanceof \Tk\Db\Model) continue;
                if (in_array($obj->getId(), $selected)) {
                    if ($obj->resend()) {
                        $s++;
                    }
                    $i++;
                }
            }
            $p = '';
            if ($s > 1) {
                $p = '`s';
            }
            \Mod\Notice::addSuccess($s . ' record'.$p.' of ' . $i . 'successfully sent.');
        }

        $this->getUri()->redirect();
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\template You can also return HTML string
     */
    public function getHtml($list)
    {
        $js = sprintf('submitForm(document.getElementById(\'%s\'), \'%s\');',
            $this->getTable()->getForm()->getId(), $this->getObjectKey($this->event));
        $js = sprintf("if(confirm('%s')) { %s } else { $(this).unbind('click'); }", $this->confirmMsg, $js);
        $ico = '';
        if ($this->getIcon()) {
            $ico = '<i class="'.$this->getIcon().'"></i> ';
        }
        return sprintf('<a class="%s btn btn-xs" href="javascript:;" onclick="%s" title="%s" onmousedown="$(window).unbind(\'beforeunload\');">%s%s</a>',
            $this->getClassString(), $js, $this->notes, $ico, $this->label);
    }


}
