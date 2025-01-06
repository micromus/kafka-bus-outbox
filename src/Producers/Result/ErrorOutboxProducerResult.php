<?php

namespace Micromus\KafkaBusOutbox\Producers\Result;

use Throwable;

final readonly class ErrorOutboxProducerResult extends OutboxProducerResult
{
    public function __construct(
        public string $topicName,
        public Throwable $exception,
        array $publishedTopics = [],
        public array $ids = []
    ) {
        parent::__construct($publishedTopics);
    }
}
