<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Savers;

use Micromus\KafkaBus\Producers\Messages\ProducerMessage;

interface ProducerMessageSaverInterface
{
    /**
     * @param ProducerMessage[] $messages
     * @return void
     */
    public function save(array $messages): void;
}
