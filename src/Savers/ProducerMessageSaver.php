<?php

namespace Micromus\KafkaBusOutbox\Savers;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

final class ProducerMessageSaver implements ProducerMessageSaverInterface
{
    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected string $connectionName,
        protected string $topicName,
        protected array $additionalOptions,
    ) {
    }

    public function save(array $messages): void
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
