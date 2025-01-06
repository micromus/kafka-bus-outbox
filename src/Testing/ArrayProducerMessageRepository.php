<?php

namespace Micromus\KafkaBusOutbox\Testing;

use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;
use Micromus\KafkaBusOutbox\Testing\Exceptions\OutboxProducerMessagesEndedException;

final class ArrayProducerMessageRepository implements ProducerMessageRepositoryInterface
{
    private int $id = 0;
    /**
     * @param DeferredOutboxProducerMessage[] $outboxProducerMessages
     */
    public function __construct(
        public array $outboxProducerMessages = [],
    ) {
    }

    /**
     * @param int $limit
     * @return DeferredOutboxProducerMessage[]
     *
     * @throws OutboxProducerMessagesEndedException
     */
    public function get(int $limit = 100): array
    {
        $messages = array_slice($this->outboxProducerMessages, 0, $limit);

        if (count($messages) == 0) {
            throw new OutboxProducerMessagesEndedException();
        }

        return $messages;
    }

    public function save(array $messages): void
    {
        $this->outboxProducerMessages += array_map($this->mapDeferredOutboxProducerMessage(...), $messages);
    }

    public function delete(array $ids): void
    {
        $this->outboxProducerMessages = array_filter(
            $this->outboxProducerMessages,
            fn (DeferredOutboxProducerMessage $message) => !in_array($message->id, $ids)
        );
    }

    private function mapDeferredOutboxProducerMessage(OutboxProducerMessage $message): DeferredOutboxProducerMessage
    {
        return new DeferredOutboxProducerMessage(
            id: (string) $this->id++,
            producerMessage: $message
        );
    }
}
