<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBusOutbox\Exceptions\CannotPublishMessageForTopicException;
use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerStreamInterface;
use Micromus\KafkaBusOutbox\Producers\Result\ErrorOutboxProducerResult;

final class OutboxProducerStream implements OutboxProducerStreamInterface
{
    protected bool $forceStop = false;

    public function __construct(
        protected OutboxProducerInterface $producer,
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected int $timeToSleep = 60,
        protected int $limit = 100
    ) {
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }

    public function process(bool $once = false): void
    {
        do {
            $messages = $this->getProducerMessages();

            if (count($messages) === 0) {
                sleep($this->timeToSleep);

                continue;
            }

            $this->publishProducerMessages($messages);
        }
        while (!$this->forceStop && !$once);
    }

    private function getProducerMessages(): array
    {
        return $this->producerMessageRepository
            ->get($this->limit);
    }

    /**
     * @param array $messages
     * @return void
     *
     * @throws CannotPublishMessageForTopicException
     */
    private function publishProducerMessages(array $messages): void
    {
        $producerResult = $this->producer
            ->publish($messages);

        // Delete published messages from repository
        foreach ($producerResult->publishedTopics as $publishedTopic) {
            $this->producerMessageRepository
                ->delete($publishedTopic->ids);
        }

        // Trigger throw if published message is negative
        if ($producerResult instanceof ErrorOutboxProducerResult) {
            $messageCount = count($producerResult->ids);

            throw new CannotPublishMessageForTopicException(
                "Cannot publish $messageCount messages in topic {$producerResult->topicName}",
                previous: $producerResult->exception
            );
        }
    }
}
