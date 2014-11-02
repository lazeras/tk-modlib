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
 * @package Adm\Module\Dev
 */
class Table extends \Mod\Module
{

    /**
     * @var Table
     */
    protected $table = null;



    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Tables Example');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);

    }

    /**
     * init
     *
     */
    function init()
    {

        $ff = \Form\Factory::getInstance();
        $tf = \Table\Factory::getInstance();

        $this->table = $tf->createTable('Table');
        $this->table->addCell($tf->createCellCheckbox());
        $this->table->addCell($tf->createCellInteger('id'));
        $this->table->addCell($tf->createCellString('name'))->setKey()->setUrl(\Tk\Url::create('/admin/null.html'));
        $this->table->addCell($tf->createCellEmail('email'));
        $this->table->addCell($tf->createCellBoolean('active'));
        $this->table->addCell($tf->createCellDate('created'));

        $this->table->addAction($tf->createActionDelete());
        $this->table->addAction($tf->createActionCsv());
        $this->table->addAction($tf->createActionFieldVis());
        
        $list = array(array('-- Select --', ''), array('Option 1', '1'), array('Option 2', '2'), array('Option 3', '3'));
        $this->table->addFilter($ff->createFieldSelect('category', $list));
        $this->table->addFilter($ff->createFieldText('keywords'));
        $this->table->addFilter($ff->createFieldDate('dateFrom', true));
        $this->table->addFilter($ff->createFieldDate('dateTo', true));

        $this->addChild($tf->createTableRenderer($this->table), 'Table');

    }


    public function doDefault()
    {
        $result = array();

        for ($i = 0; $i < 10; $i++) {
            $row = array(
              'id' => $i,
              'name' => ucfirst($this->getRandStr(9)),
              'email' => $this->getRandStr(\Tk\Math::rand(5, 10)).'@'.$this->getRandStr(\Tk\Math::rand(5, 10)).'.com',
              'active' => \Tk\Math::rand(0, 1) ? true : false,
              'created' => \Tk\Date::create()
            );
            $result[] = $row;
        }
        $list = new \Tk\Db\ArrayObject($result);


        $dbTool = \Tk\Db\Tool::create('', 10, 0, 60);
        $this->table->setList($list, $dbTool);

    }



    /**
     *
     *
     *
     * @param type $length
     * @param type $mode
     * @return string
     */
    function getRandStr($length = 10, $mode = 0)
    {
        switch ($mode) {
            default:
            case 0:
                $characters = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 1:
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 2:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 3:
                $characters = '0123456789';
                break;
            case 4:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[\Tk\Math::rand(0, strlen($characters) - 1)];
        }
        return $randomString;
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
  <div var="Table"></div>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}
