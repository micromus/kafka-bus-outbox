<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;

final class MessageGrouper
{
    /**
     * @param DeferredOutboxProducerMessage[] $messages
     * @return array<string, array<string, array{options: array, messages: DeferredOutboxProducerMessage[]}>>
     */
    public function group(array $messages): array
    {
        $connections = [];

        foreach ($messages as $message) {
            $producerMessage = $message->producerMessage;

            if (isset($connections[$producerMessage->connectionName][$producerMessage->topicName])) {
                $connections[$producerMessage->connectionName][$producerMessage->topicName]['messages'][] = $message;

                continue;
            }

            $connections[$producerMessage->connectionName][$producerMessage->topicName] = [
                'options' => $producerMessage->additionalOptions,
                'messages' => [$message],
            ];
        }

        return $connections;
    }
}
