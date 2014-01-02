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
        $messageMandrill = json_decode($vars['mandrill_events'], true);
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
        $message->setRaw($messageMandrill[0]['msg']['raw_msg']);
        $message->setHeaders($messageMandrill[0]['msg']['headers']);
        $message->setText($messageMandrill[0]['msg']['text']);
        $message->setHtml($messageMandrill[0]['msg']['html']);
        $message->setSubject($messageMandrill[0]['msg']['subject']);
        $message->setFrom(
            $messageMandrill[0]['msg']['from_email'],
            $messageMandrill[0]['msg']['from_name']
        );
        $message->setTo($messageMandrill[0]['msg']['to']);
        if (isset($messageMandrill[0]['msg']['cc'])) {
            $message->setCc($messageMandrill[0]['msg']['cc']);
        }
        $message->source = json_decode($vars['mandrill_events'])[0];

        return $message;
    }
}
