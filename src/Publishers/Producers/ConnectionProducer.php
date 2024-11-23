<?php

namespace Micromus\KafkaBusOutbox\Publishers\Producers;

use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\Configuration;

class ConnectionProducer
{
    protected array $producers = [];

    public function __construct(
        protected ConnectionInterface $connection
    ) {
    }

    public function getOrCreateProducer(string $topicName, array $options = []): ProducerInterface
    {
        if (!isset($this->producers[$topicName])) {
            $this->producers[$topicName] = $this->connection->createProducer($topicName, new Configuration($options));
        }

        return $this->producers[$topicName];
    }
}
