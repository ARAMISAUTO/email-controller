<?php
namespace ConstructionsIncongrues\EmailController\Event;

use Symfony\Component\EventDispatcher\Event;
use ConstructionsIncongrues\EmailController\Message;

class MessageEvent extends Event
{
    private $message;
    private $data = array();

    public function __construct(Message $message, $data = array())
    {
        $this->message = $message;
        $this->data = $data;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }
}
