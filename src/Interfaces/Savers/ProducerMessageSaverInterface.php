<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Savers;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

interface ProducerMessageSaverInterface
{
    /**
     * @param iterable<ProducerMessage> $messages
     * @return void
     */
    public function save(iterable $messages): void;
}
