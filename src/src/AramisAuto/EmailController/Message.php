<?php
namespace AramisAuto\EmailController;

class Message
{
    public $raw;
    public $headers;
    public $text;
    public $html;
    public $subject;
    public $from = array();
    public $to = array();
    public $cc = array();
    public $source;
}
