<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Consumers\Configuration as ConsumerConfiguration;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Configuration as ProducerConfiguration;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\UuidGeneratorInterface;

class OutboxKafkaConnection implements ConnectionInterface
{
    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected ConnectionRegistryInterface $connectionRegistry,
        protected UuidGeneratorInterface $uuidGenerator,
        protected string $sourceConnectionName,
    ) {
    }

    public function createProducer(string $topicName, ProducerConfiguration $configuration): ProducerInterface
    {
        return new OutboxProducer(
            topicName: $topicName,
            connectionName: $this->sourceConnectionName,
            additionalOptions: $configuration->additionalOptions,
            producerMessageRepository: $this->producerMessageRepository,
            uuidGenerator: $this->uuidGenerator,
        );
    }

    public function createConsumer(array $topicNames, ConsumerConfiguration $configuration): ConsumerInterface
    {
        return $this->connectionRegistry->connection($this->sourceConnectionName)
            ->createConsumer($topicNames, $configuration);
    }
}
