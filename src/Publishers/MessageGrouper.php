<?php

namespace Micromus\KafkaBusOutbox\Publishers;

use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

class MessageGrouper
{
    /**
     * @param OutboxProducerMessage[] $messages
     * @return array<string, array<string, array{options: array, messages: OutboxProducerMessage[]}>>
     */
    public function group(array $messages): array
    {
        $connections = [];

        foreach ($messages as $message) {
            if (isset($connections[$message->connectionName][$message->topicName])) {
                $connections[$message->connectionName][$message->topicName]['messages'][] = $message;

                continue;
            }

            $connections[$message->connectionName][$message->topicName] = [
                'options' => $message->additionalOptions,
                'messages' => [$message],
            ];
        }

        return $connections;
    }
}
