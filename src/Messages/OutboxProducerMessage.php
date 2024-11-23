<?php

namespace Micromus\KafkaBusOutbox\Messages;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

final readonly class OutboxProducerMessage
{
    public function __construct(
        public string $id,
        public string $connectionName,
        public string $topicName,
        public ProducerMessage $message,
        public array $additionalOptions = []
    ) {
    }
}
