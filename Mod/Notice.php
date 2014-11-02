<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 *
 *
 * @package Mod
 */
class Notice extends Renderer implements \Serializable
{
    const SID = '_\Mod\Notice';


    const TYPE_WARNING = 'warning';
    const TYPE_SUCCESS = 'success';
    const TYPE_INFO    = 'info';
    const TYPE_ERROR   = 'danger';


    /**
     * @var \Mod\Notice
     */
    static $instance = null;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var bool
     */
    protected $hasMessages = false;

    /**
     * @var bool
     */
    protected $shown = false;



    /**
     * Get an instance of this object
     *
     * @return \Mod\Notice
     */
    static function getInstance()
    {
        if (!self::$instance) {
            if (\Tk\Session::getInstance()->exists(self::SID)) {
                self::$instance = \Tk\Session::getInstance()->get(self::SID);
            } else {
                self::$instance = new self();
                \Tk\Session::getInstance()->set(self::SID, self::$instance);
            }
        }
        return self::$instance;
    }

    /**
     * Sigleton, No instances can be created.
     * Use:
     *   \Mod\Message::getInstance()
     */
    private function __construct()
    {

    }

    public function __wakeup()
    {
        $this->template = null;
    }

    public function __sleep()
    {
        $this->template = null;
        if ($this->shown) {
            $this->clear();
        }
        return array('messages');
    }


    public function serialize()
    {
        $this->template = null;
        if ($this->shown) {
            $this->clear();
        }
        return serialize(array('messages' => $this->messages));
    }


    public function unserialize($data)
    {
        $arr = unserialize($data);
        $this->messages = $arr['messages'];
    }

    static function addSuccess($message, $title = 'Success')
    {
        self::add($message, $title, self::TYPE_SUCCESS, 'icon-ok-sign');
    }
    static function addWarning($message, $title = 'Warning')
    {
        self::add($message, $title, self::TYPE_WARNING, 'icon-warning-sign');
    }
    static function addError($message, $title = 'Error')
    {
        self::add($message, $title, self::TYPE_ERROR, 'icon-remove-sign');
    }
    static function addInfo($message, $title = 'Information')
    {
        self::add($message, $title, self::TYPE_INFO, 'icon-exclamation-sign');
    }

    /**
     * Check if there are any messages
     *
     * @return bool
     */
    static function hasMessages()
    {
        return count(self::getInstance()->messages);
    }

    /**
     * Clear the message list
     *
     * @return \Mod\Notice
     */
    public function clear()
    {
        $this->messages = array(
            self::TYPE_SUCCESS => array(),
            self::TYPE_WARNING => array(),
            self::TYPE_ERROR => array(),
            self::TYPE_INFO => array()
        );
        return $this;
    }


    /**
     * Get message list
     *
     * @param string $type
     * @return array
     */
    public function getMessageList($type = '')
    {
        if (isset($this->messages[$type])) {
            return $this->messages[$type];
        }
        return $this->messages;
    }

    /**
     * add a message to display on next page load
     *
     * @param string $message
     * @param string $title
     * @param string $type Use the constants \Mod\Notice::TYPE_INFO, etc
     * @param string $icon
     */
    static function add($message, $title = 'Warning', $type = '', $icon = '')
    {
        $css = '';
        if ($type) {
            $css = 'alert-'.$type;
        }
        $title = htmlentities($title);
        $message = $message;
        $data = array('message' => $message, 'title' => $title, 'css' => $css);
        if ($icon) {
            $data['icon'] = $icon;
        }

        self::getInstance()->messages[$type][] = $data;
        self::getInstance()->notify('add');
    }


    /**
     * show
     *
     * @return string
     */
    public function show()
    {
        if (!self::hasMessages() || $this->shown) return;
        $this->shown = true;
        $template = $this->getTemplate();
        $this->notify('show');
        
        
        foreach ($this->messages as $msgList) {
            foreach ($msgList as $data) {
                $repeat = $template->getRepeat('row');
                $repeat->insertText('title', $data['title']);
                $repeat->insertHtml('message', $data['message']);
                $repeat->addClass('row', $data['css']);
                if (isset($data['icon'])) {
                    $repeat->addClass('icon', $data['icon']);
                    $repeat->setChoice('icon');
                }
                $repeat->appendRepeat();
                $template->setChoice('show');
            }
        }

    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<div class="row" choice="show">
  <div class="col-lg-12">
      <div class="alert" var="row" repeat="row">
        <button class="close noblock" data-dismiss="alert">&times;</button>
        <h4><i choice="icon" var="icon"></i> <strong var="title"></strong></h4>
        <span var="message"></span>
      </div>
  </div>
</div>
XML;
        $template = Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}
