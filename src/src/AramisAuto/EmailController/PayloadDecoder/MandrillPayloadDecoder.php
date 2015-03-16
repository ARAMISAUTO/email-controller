<?php
namespace AramisAuto\EmailController\PayloadDecoder;

use AramisAuto\EmailController\Exception\InvalidPayloadException;
use AramisAuto\EmailController\Exception\MandrillWebhookTestException;
use AramisAuto\EmailController\Message;

class MandrillPayloadDecoder implements PayloadDecoderInterface
{
    public function decode($payload)
    {
        // Parse request raw body
        $vars = array();
        parse_str($payload, $vars);

        if (!isset($vars['mandrill_events'])) {
            throw new InvalidPayloadException(
                sprintf(
                    'Missing "mandrill_events" body parameter - %s',
                    json_encode(array('payload' =>  $payload), JSON_UNESCAPED_SLASHES)
                )
            );
        }

        // Decode JSON
        $messagesMandrill = json_decode($vars['mandrill_events'], true);

        if (is_array($messagesMandrill) && count($messagesMandrill) === 0) {
            throw new MandrillWebhookTestException(
                sprintf(
                    'Received Mandrill test payload - %s',
                    json_encode(array('payload' =>  $payload), JSON_UNESCAPED_SLASHES)
                )
            );
        }

        if (!$messagesMandrill) {
            throw new InvalidPayloadException(
                sprintf(
                    'Could not decode payload JSON - %s',
                    json_encode($vars['mandrill_events'], JSON_UNESCAPED_SLASHES)
                )
            );
        }

        $messages = array();
        foreach ($messagesMandrill as $messageMandrill) {
            // Defaults
            $messageDefaults = array(
                'cc'         => array(),
                'from_email' => null,
                'from_name'  => null,
                'headers'    => array(),
                'html'       => null,
                'id'         => null,
                'metadata'   => array(),
                'raw_msg'    => null,
                'subject'    => null,
                'text'       => null,
                'to'         => array(),
            );
            $messageFields = array_merge($messageDefaults, $messageMandrill);

            // Create EmailController message
            $message = new Message();
            $message->setRaw($messageFields['raw_msg']);
            $message->setHeaders($messageFields['headers']);
            $message->setText($messageFields['text']);
            $message->setHtml($messageFields['html']);
            $message->setSubject($messageFields['subject']);
            $message->setFrom(
                $messageFields['from_email'],
                $messageFields['from_name']
            );
            $message->setTo($messageFields['to']);
            $message->setCc($messageFields['cc']);
            $message->source = json_decode($vars['mandrill_events'], true)[0];
            $message->setId($message->source['_id']);
            if (isset($message->source['msg']['metadata'])) {
                $message->metadata = $message->source['msg']['metadata'];
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
