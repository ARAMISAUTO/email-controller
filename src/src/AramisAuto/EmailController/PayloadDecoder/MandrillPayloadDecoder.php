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
                    json_encode(array('payload' => $payload), JSON_UNESCAPED_SLASHES)
                )
            );
        }

        // Decode JSON
        $messagesMandrill = json_decode($vars['mandrill_events'], true);

        if (is_array($messagesMandrill) && count($messagesMandrill) === 0) {
            throw new MandrillWebhookTestException(
                sprintf(
                    'Received Mandrill test payload - %s',
                    json_encode(array('payload' => $payload), JSON_UNESCAPED_SLASHES)
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
                'cc' => array(),
                'from_email' => null,
                'from_name' => null,
                'headers' => array(),
                'html' => null,
                'id' => null,
                'metadata' => array(),
                'raw_msg' => null,
                'subject' => null,
                'text' => null,
                'email' => array(),
            );
            $messageFields = array_merge($messageDefaults, $messageMandrill);

            // Create EmailController message
            $message = new Message();
            $message->setRaw($messageFields['raw_msg']);
            $message->setHeaders($messageFields['headers']);
            $message->setText($messageFields['text']);
            $message->setHtml($messageFields['html']);
            $message->setSubject($messageFields['subject']);
            $message->setFrom($messageFields['msg']['sender']);
            $message->setTo(array(array($messageMandrill['msg']['email'], null)));

            // Message unique identifier
            $message->setId($messageFields['_id']);

            // Message metadata
            $message->metadata = array();
            if (isset($messageFields['msg'])) {
                $message->metadata = array_merge($message->metadata, $messageFields['msg']);
            }
            if (isset($messageFields['msg']) && isset($messageFields['msg']['metadata'])) {
                $message->metadata = array_merge($message->metadata, $messageFields['msg']['metadata']);
            }

            // Keep original message
            $message->source = $messageMandrill;

            $messages[] = $message;
            unset($messageFields);
        }

        $this->logger->info('Mandrill payload decoded', ['messagesCount' => count($messages)]);

        return $messages;
    }
}
