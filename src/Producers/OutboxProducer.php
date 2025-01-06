<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBus\Interfaces\Producers\ProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Messages\DeferredOutboxProducerMessage;
use Micromus\KafkaBusOutbox\Producers\Result\ErrorOutboxProducerResult;
use Micromus\KafkaBusOutbox\Producers\Result\OutboxProducerResult;
use Micromus\KafkaBusOutbox\Producers\Result\SuccessOutboxProducerResult;
use Micromus\KafkaBusOutbox\Producers\Result\PublishedTopicResult;
use Throwable;

final class OutboxProducer implements OutboxProducerInterface
{
    protected MessageGrouper $messageGrouper;

    public function __construct(
        protected ProducerBag $producerBag,
    ) {
        $this->messageGrouper = new MessageGrouper();
    }

    /**
     * @param DeferredOutboxProducerMessage[] $messages
     * @return OutboxProducerResult
     */
    public function publish(array $messages): OutboxProducerResult
    {
        $groupedOutboxMessages = $this->messageGrouper
            ->group($messages);

        $published = [];

        foreach ($groupedOutboxMessages as $connectionName => $topics) {
            foreach ($topics as $topicName => $topicConfiguration) {
                $messageIds = array_map(fn (DeferredOutboxProducerMessage $message) => $message->id, $messages);

                try {
                    $producer = $this->producerBag
                        ->getOrCreateProducer($connectionName, $topicName, $topicConfiguration['options']);

                    $this->publishMessages($producer, $topicConfiguration['messages']);

                    $published[] = new PublishedTopicResult($topicName, $messageIds);
                }
                catch (Throwable $exception) {
                    return new ErrorOutboxProducerResult($topicName, $exception, $published, $messageIds);
                }
            }
        }

        return new SuccessOutboxProducerResult($published);
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
    }
}
