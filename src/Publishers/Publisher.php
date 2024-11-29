<?php

namespace Micromus\KafkaBusOutbox\Publishers;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Publishers\PublisherInterface;
use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;
use Micromus\KafkaBusOutbox\Publishers\Producers\ProducerManager;

class Publisher implements PublisherInterface
{
    protected MessageGrouper $messageGrouper;

    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected ProducerManager $producerManager,
    ) {
        $this->messageGrouper = new MessageGrouper();
    }

    /**
     * @param OutboxProducerMessage[] $messages
     * @return void
     */
    public function publish(array $messages): void
    {
        $groupedOutboxMessages = $this->messageGrouper
            ->group($messages);

        foreach ($groupedOutboxMessages as $connectionName => $topics) {
            foreach ($topics as $topicName => $topicConfiguration) {
                $producer = $this->producerManager
                    ->getOrCreateProducer($connectionName, $topicName, $topicConfiguration['options']);

                $this->publishMessages($producer, $topicConfiguration['messages']);
            }
        }
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
