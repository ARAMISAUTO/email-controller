<?php
namespace AramisAuto\EmailController;

use AramisAuto\EmailController\Event\ErrorEvent;
use AramisAuto\EmailController\Event\MessageEvent;
use AramisAuto\EmailController\Exception\NoMessageStrategyException;
use AramisAuto\EmailController\MessageStrategy\AbstractMessageStrategy;
use PayloadDecoder\PayloadDecoderInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;

class EmailController implements LoggerInterface
{
    use Psr\Log\LoggerAwareTrait;

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
        foreach ($this->messageStrategies as $expression => $spec) {
            if ($language->evaluate($expression, array('message' => $message))) {
                $matched = true;

                // Instanciate strategy
                $strategy = $spec[0];
                $strategy->setMessage($message);

                // Global success event
                $strategy->on($strategy->success(), function (MessageEvent $event) {
                    $this->getEventDispatcher()->dispatch($this->success(), $event);
                });

                // Global error event
                $strategy->on($strategy->error(), function (ErrorEvent $event) {
                    $this->getEventDispatcher()->dispatch($this->error(), $event);
                });

                // Execute strategy
                $strategy->execute();

                // Continue to search for strategies applicable to message ?
                if ($spec[1] !== true) {
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
        $strategy->setEventDispatcher($this->getEventDispatcher());
        $this->messageStrategies[$expression] = array($strategy, $continue);
    }

    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function on($eventName, $callback)
    {
        $this->getEventDispatcher()->addListener($eventName, $callback);
    }

    public function error()
    {
        return 'emailcontroller.error';
    }

    public function success()
    {
        return 'emailcontroller.success';
    }
}
