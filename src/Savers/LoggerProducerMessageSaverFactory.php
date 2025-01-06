<?php

namespace Micromus\KafkaBusOutbox\Savers;

use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverFactoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverInterface;

final class LoggerProducerMessageSaverFactory implements ProducerMessageSaverFactoryInterface
{
    public function __construct(
        protected BusLoggerInterface $logger,
        protected ProducerMessageSaverFactoryInterface $producerMessageSaverFactory,
    ) {
    }

    public function create(string $connectionName, string $topicName, array $additionalOptions): ProducerMessageSaverInterface
    {
        return new LoggerProducerMessageSaver(
            $this->logger,
            $connectionName,
            $topicName,
            $this->producerMessageSaverFactory->create($connectionName, $topicName, $additionalOptions)
        );
    }
}
