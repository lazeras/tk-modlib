<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */
namespace Mod\Db;

/**
 * 
 *
 * @package Mail\Db
 */
class Log extends \Tk\Db\Model
{
    
    /**
     * @var int
     */
    public $id = 0;



    /**
     * @var bool
     */
    public $sent = false;
    
    /**
     * @var string
     */
    public $to = '';
    
    /**
     * @var string
     */
    public $from = '';
    
    /**
     * @var string
     */
    public $cc = '';
    
    /**
     * @var string
     */
    public $bcc = '';
    
    /**
     * @var string
     */
    public $subject = '';
    
    /**
     * @var string
     */
    public $body = '';
    
    /**
     * @var string
     */
    public $notes = '';
    
    /**
     * @var \Tk\Date
     */
    public $modified = null;
    
    /**
     * @var \Tk\Date
     */
    public $created = null;
    


    /**
     * __construct
     *
     */
    function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
        
    }


    /**
     * This function has been attached to the Tk\Mail\Gateway object
     * and will be called once the email is sent.
     *
     * @param \Tk\Mail\Message $message
     * @param bool $isSent
     * @return \Mail\Db\Log
     */
    static function gatewayCallback($message, $isSent)
    {
        $log = new self();
        $log->to = \Tk\Mail\Message::listToStr($message->getTo());
        $log->from = current($message->getFrom());
        $log->cc = \Tk\Mail\Message::listToStr($message->getCc());
        $log->bcc = \Tk\Mail\Message::listToStr($message->getBcc());
        
        $log->subject = $message->getSubject();
        $log->body = $message->getBody();
        $log->sent = $isSent;
        
        //TODO: Cater for attachments (would have to base64 encode them)
        $log->save();
        return $log;
    }


    /**
     * Attempt to resend this email
     *
     * @return bool
     */
    public function resend()
    {
        $message = $this->recoverMessage();
        return $message->send();
    }


    /**
     * Attempt to recover the message object from
     * the log.
     *
     * @return \Tk\Mail\Message
     */
    public function recoverMessage()
    {
        $message = $this->getConfig()->createMailMessage();
        $message->setFrom($this->from);
        $message->setSubject($this->subject);
        $message->setBody($this->body);
        if ($this->body == strip_tags($this->body)) {   // isHtml ?
            $message->isHtml(false);
        }
        $message->addTo($this->to);
        $message->addCc($this->cc);
        $message->addBcc($this->bcc);
        
        return $message;
    }

}

/**
 * A validator object for `Mail\Db\Log`
 *
 * @package Mail\Db
 */
class LogValidator extends \Tk\Validator
{

    /**
     * @var \Mail\Db\Log
     */
    protected $obj = null;

    /**
     * Validates
     *
     */
    public function validate()
    {
        if (!preg_match('/^.*$/', $this->obj->to)) {
            $this->addError('to', 'Invalid To Value.');
        }
        if (!preg_match('/^.*$/', $this->obj->from)) {
            $this->addError('from', 'Invalid From Value.');
        }
        if (!preg_match('/^.*$/', $this->obj->cc)) {
            $this->addError('cc', 'Invalid Cc Value.');
        }
        if (!preg_match('/^.*$/', $this->obj->bcc)) {
            $this->addError('bcc', 'Invalid Bcc Value.');
        }
        if (!preg_match('/^.*$/', $this->obj->subject)) {
            $this->addError('subject', 'Invalid Subject Value.');
        }
    }

}