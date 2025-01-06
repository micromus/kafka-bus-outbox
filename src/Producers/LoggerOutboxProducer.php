<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBus\Interfaces\BusLoggerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Producers\Result\ErrorOutboxProducerResult;
use Micromus\KafkaBusOutbox\Producers\Result\OutboxProducerResult;

final class LoggerOutboxProducer implements OutboxProducerInterface
{
    public function __construct(
        protected BusLoggerInterface $logger,
        protected OutboxProducerInterface $producer,
    ) {
    }

    public function publish(array $messages): OutboxProducerResult
    {
        $producerResult = $this->producer
            ->publish($messages);

        foreach ($producerResult->publishedTopics as $publishedTopic) {
            $messagesCount = count($publishedTopic->ids);

            $this->logger
                ->debug("Published topic #{$publishedTopic->topicName} ({$messagesCount})", [
                    'topic_name' => $publishedTopic->topicName,
                    'ids' => $publishedTopic->ids,
                ]);
        }

        if ($producerResult instanceof ErrorOutboxProducerResult) {
            $messagesCount = count($producerResult->ids);

            $this->logger
                ->debug("Cannot published topic #{$producerResult->topicName} ({$messagesCount})", [
                    'exception' => $producerResult->exception,
                    'topic_name' => $producerResult->topicName,
                    'ids' => $producerResult->ids,
                ]);
        }

        return $producerResult;
    }
}
