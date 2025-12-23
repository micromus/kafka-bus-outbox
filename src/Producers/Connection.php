<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBus\Interfaces\Connections\ConnectionInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBus\Producers\ProducerConfig;
use Micromus\KafkaBus\Topics\Topic;

final class Connection
{
    protected array $producers = [];

    public function __construct(
        protected ConnectionInterface $connection
    ) {
    }

    public function getOrCreateProducer(Topic $topic, array $options = []): ProducerInterface
    {
        if (!isset($this->producers[$topic->key])) {
            $this->producers[$topic->key] = $this->connection
                ->createProducer($topic, new ProducerConfig($options));
        }

        return $this->producers[$topic->key];
    }
}
