<?php
namespace AramisAuto\EmailController;

use AramisAuto\EmailController\Event\ErrorEvent;
use AramisAuto\EmailController\Event\MessageEvent;
use AramisAuto\EmailController\Exception\NoMessageStrategyException;
use AramisAuto\EmailController\MessageStrategy\AbstractMessageStrategy;
use PayloadDecoder\PayloadDecoderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class EmailController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $eventDispatcher;
    private $payloadDecoder;
    private $messageStrategies = array();

    public function __construct(PayloadDecoder\PayloadDecoderInterface $payloadDecoder, LoggerInterface $logger = null)
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->payloadDecoder = $payloadDecoder;
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        $this->setLogger($logger);
    }

    public function run($payload)
    {
        // Decode payload
        $this->logger->info(
            'Using payload decoder',
            array('payloadDecoder' => get_class($this->payloadDecoder))
        );
        $messages = $this->payloadDecoder->decode($payload);

        // Execute applicable message strategies
        foreach ($messages as $message) {
            $matched = false;
            $language = new ExpressionLanguage();
            foreach ($this->messageStrategies as $expression => $spec) {
                // Log
                $this->logger->info(
                    'Evaluating expression against message',
                    array(
                        'expression' => $expression,
                        'messageId'  => $message->id
                    )
                );
                $this->logger->debug(
                    'Evaluating expression against message',
                    array(
                        'expression' => $expression,
                        'message'    => $message,
                        'messageId'  => $message->id
                    )
                );

                // Evaluate expression against message
                if ($language->evaluate($expression, array('message' => $message))) {
                    $matched = true;

                    // Instanciate strategy
                    $strategy = $spec[0];
                    $strategy->setMessage($message);

                    // Log
                    $this->logger->info(
                        'Expression matched, executing related strategy',
                        array(
                            'expression' => $expression,
                            'strategy' => get_class($strategy),
                            'messageId'  => $message->id
                        )
                    );

                    // Global success event
                    $strategy->on($strategy->success(), function (MessageEvent $event) use ($strategy) {
                        $this->logger->info(
                            'Strategy execution succeeded',
                            array(
                                'strategy'  => get_class($strategy),
                                'messageId' => $strategy->getMessage()->id
                            )
                        );
                        $this->getEventDispatcher()->dispatch($this->success(), $event);
                    });

                    // Global error event
                    $strategy->on($strategy->error(), function (ErrorEvent $event) use ($strategy) {
                        $this->logger->info(
                            'Strategy execution failed',
                            array(
                                'strategy'  => get_class($strategy),
                                'messageId' => $strategy->getMessage()->id,
                                'error'     => $event->getError()
                            )
                        );
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
                        json_encode(array('message' => $message), JSON_UNESCAPED_SLASHES)
                    )
                );
            }
        }
    }

    public function addMessageStrategy($expression, AbstractMessageStrategy $strategy, $continue = false)
    {
        // Configure strategy
        $strategy->setEventDispatcher($this->getEventDispatcher());
        $strategy->setLogger($this->logger);

        // Store strategy
        $this->messageStrategies[$expression] = array($strategy, $continue);

        // Log
        $this->logger->info(
            'Added message strategy rule',
            array('expression' => $expression, 'strategy' => get_class($strategy))
        );
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
