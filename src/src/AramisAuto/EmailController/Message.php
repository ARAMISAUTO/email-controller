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

    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    public function setHeaders(array $headers)
    {
        // All headers must be case sensitive
        $headersLower = new \stdClass();
        foreach ($headers as $name => $value) {
            $nameLower = strtolower($name);
            $headersLower->$nameLower = $value;
        }
        $this->headers = $headersLower;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setFrom($email, $name = null)
    {
        $from = new \StdClass();
        $from->email = $email;
        $from->name = $name;
        $this->from = $from;
    }

    public function setTo(array $tos)
    {
        foreach ($tos as $to) {
            $objTo = new \StdClass();
            $objTo->email = $to[0];
            $objTo->name = $to[1];
        }
        $this->to = $objTo;
    }

    public function setCc(array $ccs)
    {
        foreach ($ccs as $cc) {
            $objCc = new \StdClass();
            $objCc->email = $cc[0];
            $objCc->name = $cc[1];
        }
        $this->cc = $objCc;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }
}
