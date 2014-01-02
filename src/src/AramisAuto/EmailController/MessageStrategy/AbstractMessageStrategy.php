<?php
namespace AramisAuto\EmailController\MessageStrategy;

use AramisAuto\EmailController\Message;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractMessageStrategy implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $eventDispatcher;
    private $eventError;
    private $eventSuccess;
    private $message;

    public function __construct()
    {
        $this->eventError = sprintf('emailcontroller.%s.error', uniqid());
        $this->eventSuccess = sprintf('emailcontroller.%s.success', uniqid());
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

    public function getMessage()
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
