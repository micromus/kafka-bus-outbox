<?php

namespace Micromus\KafkaBusOutbox;

use Micromus\KafkaBus\Connections\Config\Options;
use Micromus\KafkaBus\Consumers\ConsumerConfig;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Consumers\ConsumerInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\ProducerConfig;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverFactoryInterface;

final class OutboxKafkaConnection implements ConnectionInterface
{
    public function __construct(
        protected string $name,
        protected ProducerMessageSaverFactoryInterface $producerMessageSaverFactory,
        protected ConnectionRegistryInterface $connectionRegistry,
        protected string $sourceConnectionName,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): Options
    {
        return new Options();
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        $messageSaver = $this->producerMessageSaverFactory
            ->create($this->sourceConnectionName, $topic->name, $config->additionalOptions);

        return new OutboxProducer($messageSaver);
    }

    public function createConsumer(array $topics, ConsumerConfig $config): ConsumerInterface
    {
        return $this->connectionRegistry->connection($this->sourceConnectionName)
            ->createConsumer($topics, $config);
    }
}
