<?php
namespace AramisAuto\EmailController\MessageStrategy;

use AramisAuto\EmailController\Message;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractMessageStrategy
{
    private $eventDispatcher;
    private $message;
    private $eventSuccess;
    private $eventError;

    public function __construct()
    {
        $this->eventSuccess = sprintf('emailcontroller.%s.success', uniqid());
        $this->eventError = sprintf('emailcontroller.%s.error', uniqid());
    }

    public function success()
    {
        return $this->eventSuccess;
    }

    public function error()
    {
        return $this->eventError;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getMessage()
    {
        return $this->message;
    }

    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    public function on($eventName, $callback)
    {
        $this->getEventDispatcher()->addListener($eventName, $callback);
    }

    abstract public function execute();
}
