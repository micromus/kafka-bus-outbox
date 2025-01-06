<?php

namespace Micromus\KafkaBusOutbox\Savers;

use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverFactoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;

final class ProducerMessageSaverFactory implements ProducerMessageSaverFactoryInterface
{
    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
    ) {
    }

    public function create(string $connectionName, string $topicName, array $additionalOptions): ProducerMessageSaverInterface
    {
        return new ProducerMessageSaver(
            $this->producerMessageRepository,
            $connectionName,
            $topicName,
            $additionalOptions,
        );
    }
}
