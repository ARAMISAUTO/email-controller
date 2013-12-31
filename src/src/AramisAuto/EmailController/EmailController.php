<?php
namespace AramisAuto\EmailController;

use AramisAuto\EmailController\Exception\NoMessageStrategyException;
use PayloadDecoder\PayloadDecoderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use AramisAuto\EmailController\MessageStrategy\AbstractMessageStrategy;

class EmailController
{
    private $eventDispatcher;
    private $payloadDecoder;
    private $messageStrategies = array();

    public function __construct(PayloadDecoder\PayloadDecoderInterface $payloadDecoder)
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->payloadDecoder = $payloadDecoder;
    }

    public function run($payload)
    {
        // Decode payload
        $message = $this->payloadDecoder->decode($payload);

        // Execute applicable message strategies
        $matched = false;
        $language = new ExpressionLanguage();
        foreach ($this->messageStrategies as $expression => $spec)
        {
            if ($language->evaluate($expression, array('message' => $message)))
            {
                $matched = true;

                // Instanciate strategy
                $strategy = $spec[0];
                $strategy->setMessage($message);

                // Execute strategy
                $strategy->execute();

                // Continue to search for strategies applicable to message ?
                if ($spec[1] !== true)
                {
                    break;
                }
            }
        }

        // Throw an error if no appropriate strategy was found
        if (!$matched) {
            throw new NoMessageStrategyException(
                sprintf(
                    'No applicable strategy found for message - %s',
                    json_encode(array('message' => $message))
                )
            );
        }
    }

    public function addMessageStrategy($expression, AbstractMessageStrategy $strategy, $continue = false)
    {
        $strategy->setEventDispatcher($this->eventDispatcher);
        $this->messageStrategies[$expression] = array($strategy, $continue);
    }
}
