<?php

namespace Micromus\KafkaBusOutbox\Producers\Result;

/**
 * @property string $topicName The name of the topic where the messages were sent
 * @property string[] $ids ID published messages
 */
final readonly class PublishedTopicResult
{
    public function __construct(
        public string $topicName,
        public array $ids = []
    ) {
    }
}
