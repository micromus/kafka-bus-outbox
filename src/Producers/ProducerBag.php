<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBus\Interfaces\Connections\ConnectionRegistryInterface;
use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;

final class ProducerBag
{
    /**
     * @var Connection[]
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

    private function getOrCreateConnectionProducer(string $connectionName): Connection
    {
        if (!isset($this->connections[$connectionName])) {
            $this->connections[$connectionName] = new Connection(
                $this->connectionRegistry
                    ->connection($connectionName)
            );
        }

        return $this->connections[$connectionName];
    }
}
