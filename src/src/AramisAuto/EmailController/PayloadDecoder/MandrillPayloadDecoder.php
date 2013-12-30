<?php
namespace AramisAuto\EmailController\PayloadDecoder;

use AramisAuto\EmailController\Message;
use AramisAuto\EmailController\Exception\InvalidPayloadException;

class MandrillPayloadDecoder implements PayloadDecoderInterface
{
    public function decode($payload)
    {
        // Parse request raw body
        $vars = array();
        $payload = html_entity_decode(urldecode($payload), ENT_QUOTES);
        parse_str($payload, $vars);
        if (!isset($vars['mandrill_events'])) {
            throw new InvalidPayloadException(
                sprintf(
                    'Missing "mandrill_events" body parameter - %s',
                    json_encode(array('payload' =>  $payload))
                )
            );
        }

        // Decode JSON
        $messageMandrill = json_decode($vars['mandrill_events']);
        if (!$messageMandrill) {
            throw new InvalidPayloadException(
                sprintf(
                    'Could not decode payload JSON - %s',
                    json_encode(array('json' =>  $vars['mandrill_events']))
                )
            );
        }

        // Create EmailController message
        $message = new Message();
        $message->raw = $messageMandrill[0]->msg->raw_msg;
        $message->headers = $messageMandrill[0]->msg->headers;
        $message->text = $messageMandrill[0]->msg->text;
        $message->html = $messageMandrill[0]->msg->html;
        $message->subject = $messageMandrill[0]->msg->subject;
        $message->from[0] = $messageMandrill[0]->msg->from_email;
        $message->from[1] = $messageMandrill[0]->msg->from_name;
        $message->to = $messageMandrill[0]->msg->to;
        if (isset($messageMandrill[0]->msg->cc)) {
            $message->cc = $messageMandrill[0]->msg->cc;
        }
        $message->source = $messageMandrill;

        // All headers must be case sensitive
        $headersLower = new \stdClass();
        foreach ($message->headers as $name => $value) {
            $nameLower = strtolower($name);
            $headersLower->$nameLower = $value;
        }
        $message->headers = $headersLower;

        return $message;
    }
}
