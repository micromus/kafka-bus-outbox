<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

final class OutboxProducer implements ProducerInterface
{
    public function __construct(
        protected string $topicName,
        protected string $connectionName,
        protected array $additionalOptions,
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
    ) {
    }

    public function produce(array $messages): void
    {
        $this->producerMessageRepository
            ->save(array_map($this->mapProducerMessage(...), $messages));
    }

    private function mapProducerMessage(ProducerMessage $message): OutboxProducerMessage
    {
        return new OutboxProducerMessage(
            connectionName: $this->connectionName,
            topicName: $this->topicName,
            original: $message,
            additionalOptions: $this->additionalOptions
        );
    }
}
