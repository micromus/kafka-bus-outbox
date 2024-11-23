<?php

namespace Micromus\KafkaBusOutbox\Testing;

use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

final class ArrayProducerMessageRepository implements ProducerMessageRepositoryInterface
{
    /**
     * @param OutboxProducerMessage[] $outboxProducerMessages
     */
    public function __construct(
        public array $outboxProducerMessages = [],
    ) {
    }

    public function get(int $limit = 100): array
    {
        return array_slice($this->outboxProducerMessages, 0, $limit);
    }

    public function save(array $messages): void
    {
        $this->outboxProducerMessages += $messages;
    }

    public function delete(array $ids): void
    {
        $this->outboxProducerMessages = array_filter(
            $this->outboxProducerMessages,
            fn (OutboxProducerMessage $message) => !in_array($message->id, $ids)
        );
    }
}
