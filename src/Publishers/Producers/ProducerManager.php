<?php

namespace Micromus\KafkaBusOutbox\Publishers\Producers;

use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;

class ProducerManager
{
    /**
     * @var ConnectionProducer[]
     */
    protected array $connections = [];

    public function __construct(
        protected ConnectionRegistryInterface $connectionRegistry,
    ) {
    }

    public function getOrCreateProducer(string $connectionName, string $topicName, array $options = []): ProducerInterface
    {
        return $this->getOrCreateConnectionProducer($connectionName)
            ->getOrCreateProducer($topicName, $options);
    }

    private function getOrCreateConnectionProducer(string $connectionName): ConnectionProducer
    {
        if (!isset($this->connections[$connectionName])) {
            $this->connections[$connectionName] = new ConnectionProducer(
                $this->connectionRegistry
                    ->connection($connectionName)
            );
        }

        return $this->connections[$connectionName];
    }
}
