<?php
namespace AramisAuto\EmailController\Event;

use Symfony\Component\EventDispatcher\Event;

class ErrorEvent extends Event
{
    private $error;
    private $exception;
    private $data = array();

    public function __construct($error, \Exception $exception = null, $data = array())
    {
        $this->error = $error;
        $this->exception = $exception;
        $this->data = $data;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getData()
    {
        return $this->data;
    }
}
