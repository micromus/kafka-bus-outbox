<?php

namespace Micromus\KafkaBusOutbox\Interfaces;

use Micromus\KafkaBusOutbox\Messages\OutboxProducerMessage;

interface ProducerMessageRepositoryInterface
{
    /**
     * @param int $limit
     * @return OutboxProducerMessage[]
     */
    public function get(int $limit = 100): array;

    /**
     * @param OutboxProducerMessage[] $messages
     * @return void
     */
    public function save(array $messages): void;


    /**
     * @param string[] $ids
     * @return void
     */
    public function delete(array $ids): void;
}
