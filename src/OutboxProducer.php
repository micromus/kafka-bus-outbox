<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Configuration;
use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\UuidGeneratorInterface;
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
            ->save(array_map(fn (ProducerMessage $message) => $this->map($message), $messages));
    }

    private function map(ProducerMessage $producerMessage): OutboxProducerMessage
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
