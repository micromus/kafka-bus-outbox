<?php

namespace Micromus\KafkaBusOutbox\Publishers;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;
use Micromus\KafkaBusOutbox\Publishers\Producers\ProducerManager;

class Publisher
{
    protected MessageGrouper $messageGrouper;

    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected ProducerManager $producerManager,
        protected int $limit = 100
    ) {
        $this->messageGrouper = new MessageGrouper();
    }

    public function publish(): bool
    {
        $groupedOutboxMessages = $this->getOutboxProducerMessages();

        if (count($groupedOutboxMessages) === 0) {
            return false;
        }

        foreach ($groupedOutboxMessages as $connectionName => $topics) {
            foreach ($topics as $topicName => $topicConfiguration) {
                $producer = $this->producerManager
                    ->getOrCreateProducer($connectionName, $topicName, $topicConfiguration['options']);

                $this->publishMessages($producer, $topicConfiguration['messages']);
            }
        }

        return true;
    }

    /**
     * @return array<string, array<string, array{options: array, messages: OutboxProducerMessage[]}>>
     */
    private function getOutboxProducerMessages(): array
    {
        $outboxProducerMessages = $this->producerMessageRepository
            ->get($this->limit);

        if (count($outboxProducerMessages) === 0) {
            return [];
        }

        return $this->messageGrouper
            ->group($outboxProducerMessages);
    }

    /**
     * @param ProducerInterface $producer
     * @param OutboxProducerMessage[] $messages
     * @return void
     */
    private function publishMessages(ProducerInterface $producer, array $messages): void
    {
        $producerMessages = array_map(fn (OutboxProducerMessage $message) => $message->message, $messages);

        $producer->produce($producerMessages);

        $this->producerMessageRepository
            ->delete(array_map(fn (OutboxProducerMessage $message) => $message->id, $messages));
    }
}
