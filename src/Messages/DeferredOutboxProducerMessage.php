<?php

namespace Micromus\KafkaBusOutbox\Messages;

final class DeferredOutboxProducerMessage
{
    public function __construct(
        public string $id,
        public OutboxProducerMessage $producerMessage,
    ) {
    }
}
