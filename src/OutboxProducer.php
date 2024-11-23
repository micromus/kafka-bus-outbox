<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBus\Uuid\UuidGeneratorInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

class OutboxProducer implements ProducerInterface
{
    public function __construct(
        protected string $topicName,
        protected string $connectionName,
        protected array $additionalOptions,
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected UuidGeneratorInterface $uuidGenerator
    ) {
    }

    public function produce(array $messages): void
    {
        $this->producerMessageRepository
            ->save(array_map($this->mapProducerMessage(...), $messages));
    }

    private function mapProducerMessage(ProducerMessage $producerMessage): OutboxProducerMessage
    {
        return new OutboxProducerMessage(
            id: $this->uuidGenerator->generate()->toString(),
            connectionName: $this->connectionName,
            topicName: $this->topicName,
            message: $producerMessage,
            additionalOptions: $this->additionalOptions
        );
    }
}
