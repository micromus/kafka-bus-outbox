<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Producers;

use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;

interface OutboxProducerInterface
{
    /**
     * @param DeferredOutboxProducerMessage[] $messages
     * @return void
     */
    public function publish(array $messages): void;
}
