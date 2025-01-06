<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Savers;

interface ProducerMessageSaverFactoryInterface
{
    public function create(string $connectionName, string $topicName, array $additionalOptions): ProducerMessageSaverInterface;
}
