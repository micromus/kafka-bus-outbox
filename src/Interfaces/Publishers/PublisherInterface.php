<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Publishers;

use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

interface PublisherInterface
{
    /**
     * @param OutboxProducerMessage[] $messages
     * @return void
     */
    public function publish(array $messages): void;
}
