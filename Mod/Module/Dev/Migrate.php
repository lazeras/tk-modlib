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
class Migrate extends \Mod\Module
{
    /**
     * @var array
     *
     */
    protected $list = null;

    /**
     * @var \Tk\Db\Migration
     */
    protected $migration = null;


    /**
     * __construct
     */
    public function __construct()
    {
        $this->setPageTitle('SQL Migration');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);
        
        
        $this->addEvent('exeAll', 'doExecuteAll');
        $this->addEvent('dis', 'doDisable');
        $this->addEvent('en', 'doEnable');
        $this->addEvent('exe', 'doExecutePath');


    }

    /**
     * init
     */
    public function init()
    {
        $this->migration = new \Tk\Db\Migration();
        $this->list = $this->migration->getFileList( $this->getConfig()->getLibPath() );

    }

    /**
     * Event
     */
    public function doDefault()
    {
    }

    /**
     * Event
     */
    public function doExecuteAll()
    {
        if ($this->migration->executeAll($this->list)) {
            \Mod\Notice::addSuccess('Migration Successful');
        } else {
            \Mod\Notice::addError($this->migration->getError()->getMessage());
        }
        $this->getUri()->delete('exeAll')->delete('path')->delete('class')->redirect();
    }

    /**
     * Event
     */
    public function doExecutePath()
    {
        $path = $this->getRequest()->get('path');
        $class = $this->getRequest()->get('class');

        if (!$this->migration->pathExists($path)) {
            $bakPath = $this->getConfig()->getTmpPath();
            $this->migration->getDb()->createBackup($bakPath);
            try {
                $this->migration->executeFile($path, $class);
                $this->getConfig()->getLog()->write('  M: ' . $path . ' - ' . $class);
                \Mod\Notice::addSuccess('Migration of Path `'.$path.'` Successful');
            } catch (\Exception $e) {
                $this->migration->getDb()->restoreBackup($bakPath);
                $this->getConfig()->getLog()->write($e->__toString());
                \Mod\Notice::addError($e->__toString());
            }
            if (is_file($bakPath))
                unlink($bakPath);
        }
        $this->getUri()->delete('exe')->delete('path')->delete('class')->redirect();
    }

    /**
     * Event
     */
    public function doDisable()
    {
        $path = $this->getRequest()->get('path');
        $class = $this->getRequest()->get('class');

        if (!$this->migration->pathExists($path)) {
            $this->migration->insertPath($path, $class);
            \Mod\Notice::addSuccess('Migration Path `'.$path.'` Disabled');
        }
        $this->getUri()->delete('dis')->delete('path')->delete('class')->redirect();
    }

    /**
     * Event
     */
    public function doEnable()
    {
        $path = $this->getRequest()->get('path');
        $class = $this->getRequest()->get('class');

        if ($this->migration->pathExists($path)) {
            $this->migration->deletePath($path);
            \Mod\Notice::addSuccess('Migration Path `'.$path.'` Enabled');
        }

        $this->getUri()->delete('en')->delete('path')->delete('class')->redirect();
    }


    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();

        foreach ($this->list as $o) {
            $repeat = $template->getRepeat('row');

            $repeat->insertText('path', $o->path);
            $repeat->insertText('class', $o->class);
            $stat = '';



            $repeat->setAttr('dis', 'href', $this->getUri()->set('path', $o->path)->set('class', $o->class)->set('dis'));
            $repeat->setAttr('en', 'href', $this->getUri()->set('path', $o->path)->set('class', $o->class)->set('en'));
            $repeat->setAttr('exe', 'href', $this->getUri()->set('path', $o->path)->set('class', $o->class)->set('exe'));

            if ($this->migration->pathExists($o->path)) {
                $repeat->insertText('status', 'pending');
                $repeat->setChoice('enable');
                $repeat->appendRepeat('done');
            } else {
                $repeat->insertText('status', 'installed');
                $repeat->setChoice('disable');
                $repeat->appendRepeat('not');
            }
        }


        $template->setAttr('exeAll', 'href', $this->getUri()->set('exeAll'));


        $js = <<<JS
jQuery(function($) {
  $('.exeAll').click(function (e) {
        return confirm('Are you sure you want to execute all the avalible scripts sequentially?');
  });
  $('.dis').click(function (e) {
        return confirm('Are you sure you want to disable this script?');
  });
  $('.en').click(function (e) {
        return confirm('Are you sure you want to re-enable this script?');
  });
  $('.exe').click(function (e) {
        return confirm('Are you sure you want to execute this script?');
  });
  $('.actions').click(function (e) {
        $('.clk-hide').toggle();
  });
});
JS;
        $template->appendJs($js);


        $css = <<<CSS
.table th { background-color: #EFEFEF;}
.table {
  border: 1px solid #CCC !important;
  border-width: 1px 1px 0px 0px !important;
}
.table td, .table th {
  border: 1px solid #CCC !important;
  border-width: 0px 0px 1px 1px !important;
}
CSS;
        $template->appendCss($css);


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
<div class="Migration">

  <p>
    <a href="#" class="btn btn-primary exeAll noBlock" var="exeAll">Execute</a><br/>
    Click to execute all not executed migration scripts...
  </p>
  <table border="1" cellpadding="0" cellspacing="0" class="table">
    <thead>
      <tr>
        <th class="key">Path</th>
        <th>Class</th>
        <th>Status</th>
        <th class="actions">Actions</th>
      </tr>
    </thead>
    <tr repeat="row">
      <td var="path"></td>
      <td var="class"></td>
      <td var="status"></td>
      <td>
        <a href="#" title="disable" class="btn btn-xs btn-danger dis noBlock" choice="disable" var="dis"><i class="fa fa-ban"></i></a>
        <a href="#" title="enable" class="btn btn-xs btn-success en noBlock clk-hide" choice="enable" var="en"><i class="fa fa-check"></i></a>
        <a href="#" title="execute single script" class="btn btn-xs btn-primary ex noBlock" choice="disable" var="exe"><i class="fa fa-play"></i></a>
      </td>
    </tr>
    <tbody var="not" class="not"></tbody>
    <tbody var="done" class="done"></tbody>
  </table>


</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}
