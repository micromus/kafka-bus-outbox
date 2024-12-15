<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;

class OutboxProducer implements OutboxProducerInterface
{
    protected MessageGrouper $messageGrouper;

    public function __construct(
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected ProducerManager $producerManager,
    ) {
        $this->messageGrouper = new MessageGrouper();
    }

    /**
     * @param DeferredOutboxProducerMessage[] $messages
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
     * @param DeferredOutboxProducerMessage[] $messages
     * @return void
     */
    private function publishMessages(ProducerInterface $producer, array $messages): void
    {
        $producerMessages = array_map(
            fn (DeferredOutboxProducerMessage $message) => $message->producerMessage->original,
            $messages
        );

        $producer->produce($producerMessages);

        $deleteProducerMessageIds = array_map(
            fn (DeferredOutboxProducerMessage $message) => $message->id,
            $messages
        );

        $this->producerMessageRepository
            ->delete($deleteProducerMessageIds);
    }
}
