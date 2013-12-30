<?php
namespace ConstructionsIncongrues\EmailController\MessageStrategy;

use ConstructionsIncongrues\EmailController\Message;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractMessageStrategy
{
    private $eventDispatcher;
    private $message;

    const EVENT_SUCCESS = 'emailcontroller.messagestrategy.success';
    const EVENT_ERROR   = 'emailcontroller.messagestrategy.error';
    const EVENT_START   = 'emailcontroller.messagestrategy.start';
    const EVENT_FINISH  = 'emailcontroller.messagestrategy.finish';

    public function __construct(EventDispatcher $eventDispatcher, Message $message)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->message = $message;
    }

    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    protected function getMessage()
    {
        return $this->message;
    }

    abstract public function execute();
}
