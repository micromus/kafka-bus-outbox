<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverInterface;

final class OutboxProducer implements ProducerInterface
{
    public function __construct(
        protected ProducerMessageSaverInterface $producerMessageSaver,
    ) {
    }

    public function produce(array $messages): void
    {
        $this->producerMessageSaver->save($messages);
    }
}
