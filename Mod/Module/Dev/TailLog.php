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
class TailLog extends \Mod\Module
{
    public $boxEnable = false;
    
    
    protected $logPath = '';


    /**
     * __construct
     */
    public function __construct()
    {
        $this->setPageTitle('Tail Log');
        $this->set(AdminPageInterface::CRUMBS_RESET, true);

        $this->addEvent('refresh', 'doRefresh');
        $this->logPath = $this->getConfig()->get('system.log.path');

    }

    /**
     * init
     */
    public function init()
    {

    }

    public function doDefault()
    {
    }


    public function doRefresh()
    {
        $handle = fopen($this->logPath, 'r');
        if ($this->getSession()->exists('tail-offset')) {
            $pos = $this->getSession()->get('tail-offset');
            $data = stream_get_contents($handle, -1, $pos);
            echo htmlentities($data);
            $pos = ftell($handle);
            $this->getSession()->set('tail-offset', $pos);
        } else {
            fseek($handle, 0, \SEEK_END);
            $pos = ftell($handle)-1000;
            if ($pos < 0) $pos = 0;
            $this->getSession()->set('tail-offset', $pos);
        }
        exit();
    }



    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();
        $this->getSession()->delete('tail-offset');

        $template->setAttr('input', 'src', $this->getUri()->set('refresh'));

        $url = $this->getUri()->set('refresh')->set('_disableLog');

        $template->appendJsUrl(\Tk\Url::create('/assets/jquery/plugins/jquery.timing.min.js'));
        $template->appendJsUrl(\Tk\Url::create('/assets/jquery/plugins/jquery.mousewheel.min.js'));
        $js = <<<JS
$(function() {
  var scrollEnable = true;

  function setScroll(b) {
    scrollEnable = b;
    if (b) {
        $('#tl-scroll span').text('On ');
    } else {
        $('#tl-scroll span').text('Off');
    }
  }

  $('#tl-output').bind('mousewheel', function(e, delta) {
    scrollEnable = false;
    if ($('#tl-output').scrollTop()>= $('#tl-output').get(0).scrollHeight-$('#tl-output').height()-50 ) {
        scrollEnable = true;
    }
    setScroll(scrollEnable);
  });

  $('#tl-output').scroll(function(e) {
    scrollEnable = false;
    if ($('#tl-output').scrollTop()>= $('#tl-output').get(0).scrollHeight-$('#tl-output').height()-50 ) {
        scrollEnable = true;
    }
    setScroll(scrollEnable);
  });

  $.repeat(1000, function() {
    $.get('$url', function(data) {
      var height = 0;
      if (data) {
        $('#tl-output').append(data);
      }
      if (scrollEnable) {
        height = $('#tl-output').get(0).scrollHeight;
        $('#tl-output').scrollTop(height);
      }
    });
  });

  $('#tl-close').click(function(){
    window.close();
  });

  $('#tl-clear').click(function(){
    $('#tl-output').empty();
  });
  $('#tl-scroll').click(function(){
      if (scrollEnable) {
        setScroll(false);
    } else {
        setScroll(true);
    }
  });

    setScroll(scrollEnable);
});
JS;
        $template->appendJs($js);

        $css = <<<CSS
.tl-outwindow {
  position: relative;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
  height: 600px;
}
.tl-outwindow .tl-output {
  height: 95%;
  width: 95%;
  margin: 0px auto;

  overflow-y: scroll;
  overflow-x: auto;

  font-size: 0.8em;
  background-color: #EFEFEF;
  border: 1px solid #CCC;
  padding: 5px;
  border-radius: 0px 0px 5px 5px;

  line-height: 1.2;
}
.tl-outwindow .tl-panel {
  position: absolute;
  top: 0;
  right: 80px;
  background-color: #000;
  color: #FFF;
  padding: 2px;
  border-radius: 0px 0px 5px 5px;
  opacity: 0.8;
}
.tl-outwindow .tl-panel a {
  color: #FFF;
  padding: 2px 5px;
  display: inline-block;
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
<div class="TailLog">
  <div class="tl-outwindow">
    <pre id="tl-output" class="tl-output"></pre>
    <div class="tl-panel">
        <a href="javascript:;" id="tl-clear">Clear</a> |
        <a href="javascript:;" id="tl-scroll">Scroll: <span>On</span></a> |
        <a href="javascript:;" id="tl-close">Close</a>
    </div>
  </div>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }



}
