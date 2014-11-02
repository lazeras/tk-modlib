<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Michael Mifsud
 */
namespace Mail\Module\MailLog;
use Mod\AdminPageInterface;

/**
 * View:
 * To call this module use the following component markup in the template:
 *   <module class="Mail_Module_Log_View"></module>
 *
 * @package Mail\Module\MailLog
 */
class View extends \Mod\Module
{

    /**
     * @var \Mail\Db\Log
     */
    protected $log = null;


    /**
     * __construct
     *
     */
    function __construct()
    {
        $this->setPageTitle('View Log');

        $this->addEvent('send', 'doSend');
        if ($this->getRequest()->get('logId') != null) {
            $this->log = \Mail\Db\Log::getMapper()->find($this->getRequest()->get('logId'));
        }

        $this->add(AdminPageInterface::PANEL_ACTIONS_LINKS,  \Mod\Menu\Item::create('Re-Send', $this->getUri()->set('send'), 'fa fa-envelope')->setCssClass('resend'));
    }


    /**
     * init...
     *
     */
    public function init()
    {

    }

    /**
     * Send the message
     */
    public function doSend()
    {
        if ($this->log) {
            if ($this->log->resend()) {
                \Mod\Notice::addSuccess('Message successfully re-sent.');
            } else {
                \Mod\Notice::addError('Message failed to send.');
            }
        }
        $this->getUri()->delete('send')->redirect();
    }

    /**
     * Render
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        if ($this->log == null) {
            $template->setChoice('error');
            return;
        }
        $template->setChoice('noError');

        $template->insertText('title', $this->log->subject);

        $content = $this->log->body;
        if (class_exists('tidy')) {
            $tcfg = array(
                'indent'         => true,
                'output-xhtml'   => true,
                'wrap'           => 200);
            $tidy = new \tidy();
            $tidy->parseString($content, $tcfg, 'utf8');
            $tidy->cleanRepair();
            $content = ''.$tidy;
            if (strstr($content, '</body>')) {
                $content = ''.$tidy->body();
            }
        }
        // strip styles and scripts as we do not need them in the view
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content);
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $content);
        $template->insertHtml('content', $content);

        if ($this->log->notes) {
            $template->insertHtml('notes', $this->log->notes);
            $template->setChoice('notes');
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
<div class="mod-view">
<style>
.post article {
	margin-bottom: 4em;
}
.mailBody {
  border: 1px solid #CCC;
  border-radius: 4px 4px 4px 4px;
  padding: 10px;
}
</style>
<script>
jQuery(function($) {
 $('a.resend').click(function(e) {
  if (confirm('Please confirm that you want to resend this email. Continue?')) {
    return true;
  }
  uOff();
  e.preventDefault();
  e.stopPropagation();
  return false;
 });
});
</script>
  <p choice="error" class="warningBox">No Record Found!</p>

  <article choice="noError" class="view">
    <header>
      <h2 class="title" var="title">Article Title</h2>
    </header>
    <section var="content" class="mailBody"></section>
    <section class="notes" choice="notes">
      <pre var="notes"></pre>
    </section>

  </article>

</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }
}