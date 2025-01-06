<?php

namespace Micromus\KafkaBusOutbox\Producers\Result;

/**
 * @property PublishedTopicResult[] $publishedTopics
 */
abstract readonly class OutboxProducerResult
{
    public function __construct(
        public array $publishedTopics = []
    ) {
    }
}
