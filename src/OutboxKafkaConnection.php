<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Consumers\Configuration as ConsumerConfiguration;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Configuration as ProducerConfiguration;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverFactoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;

final class OutboxKafkaConnection implements ConnectionInterface
{
    public function __construct(
        protected ProducerMessageSaverFactoryInterface $producerMessageSaverFactory,
        protected ConnectionRegistryInterface $connectionRegistry,
        protected string $sourceConnectionName,
    ) {
    }

    public function createProducer(string $topicName, ProducerConfiguration $configuration): ProducerInterface
    {
        $messageSaver = $this->producerMessageSaverFactory
            ->create($this->sourceConnectionName, $topicName, $configuration->additionalOptions);

        return new OutboxProducer($messageSaver);
    }

    public function createConsumer(array $topicNames, ConsumerConfiguration $configuration): ConsumerInterface
    {
        return $this->connectionRegistry->connection($this->sourceConnectionName)
            ->createConsumer($topicNames, $configuration);
    }
}
