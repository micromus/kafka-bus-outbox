<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Producers;

use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;
use Micromus\KafkaBusOutbox\Producers\Result\OutboxProducerResult;

interface OutboxProducerInterface
{
    /**
     * @param DeferredOutboxProducerMessage[] $messages
     * @return OutboxProducerResult
     */
    public function publish(array $messages): OutboxProducerResult;
}
