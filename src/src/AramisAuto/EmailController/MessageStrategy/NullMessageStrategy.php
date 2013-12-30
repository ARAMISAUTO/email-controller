<?php
namespace AramisAuto\EmailController\MessageStrategy;
use AramisAuto\EmailController\Event\MessageEvent;

class NullMessageStrategy extends AbstractMessageStrategy
{
    public function execute()
    {
        $event = new MessageEvent($this->getMessage());
        $this->getEventDispatcher()->dispatch(AbstractMessageStrategy::EVENT_START, $event);
        $this->getEventDispatcher()->dispatch(AbstractMessageStrategy::EVENT_SUCCESS, $event);
        $this->getEventDispatcher()->dispatch(AbstractMessageStrategy::EVENT_FINISH, $event);
    }
}
