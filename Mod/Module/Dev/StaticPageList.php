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
class StaticPageList extends \Mod\Module
{



    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Static Dispach URL List');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);

    }


    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();
        $list = $this->getConfig()->getDispatcherStatic()->getList();
        ksort($list);
        $i = 0;
        foreach ($list as $url => $class) {
            $repeat = $template->getRepeat('row');
            $css = ($i++%2) == 0 ? 'even' : 'odd';
            $repeat->addClass('row', $css);
            $repeat->insertText('url', $url);
            $repeat->setAttr('url', 'href', \Tk\Url::create($url));
            $repeat->setAttr('url', 'target', '_blank');
            $repeat->insertText('class', $class['class']);
            $repeat->appendRepeat();
        }
        
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
<div class="Util_PageList">
  <table border="0" class="table table-bordered data-table">
    <thead>
    <tr>
      <th>Url</th>
      <th>Class</th>
    </tr>
    </thead>
    <tbody>
    <tr repeat="row" var="row" class="">
      <td style="white-space: nowrap;"><a href="#" var="url"></a></td>
      <td var="class"></td>
    </tr>
    </tbody>
  </table>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }



}
