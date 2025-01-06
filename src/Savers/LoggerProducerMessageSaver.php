<?php

namespace Micromus\KafkaBusOutbox\Savers;

use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Savers\ProducerMessageSaverInterface;

final class LoggerProducerMessageSaver implements ProducerMessageSaverInterface
{
    public function __construct(
        protected BusLoggerInterface $logger,
        protected string $connectionName,
        protected string $topicName,
        protected ProducerMessageSaverInterface $producerMessageSaver
    ) {
    }

    public function save(array $messages): void
    {
        $this->producerMessageSaver->save($messages);

        foreach ($messages as $message) {
            $key = $message->key
                ? " with key {$message->key}"
                : null;

            $this->logger
                ->debug(
                    "Producer message$key #{$this->topicName} [{$this->connectionName}] for".
                        " partition #{$message->partition} saved",
                    ['connection' => $this->connectionName, 'topic_name' => $this->topicName]
                );
        }
    }
}
