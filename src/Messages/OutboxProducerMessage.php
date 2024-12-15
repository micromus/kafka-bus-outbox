<?php

namespace Micromus\KafkaBusOutbox\Messages;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

final class OutboxProducerMessage
{
    public function __construct(
        public string $connectionName,
        public string $topicName,
        public ProducerMessage $original,
        public array $additionalOptions = [],
    ) {
    }
}
