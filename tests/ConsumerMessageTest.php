<?php

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Pipelines\PipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Support\Resolvers\NativeResolver;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Consumers\MessageBuilder;
use Micromus\KafkaBus\Testing\Messages\VoidConsumerHandlerFaker;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBusOutbox\OutboxKafkaConnection;
use Micromus\KafkaBusOutbox\Testing\ArrayProducerMessageRepository;

it('can consume messages', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $driverRegistry = new DriverRegistry();

    $connectionRegistry = new ConnectionRegistry($driverRegistry, [
        'kafka' => ['driver' => 'faker', 'options' => []],
        'outbox' => ['driver' => 'outbox', 'options' => ['connection_for' => 'kafka']],
    ]);

    $producerMessageRepository = new ArrayProducerMessageRepository();

    $driverRegistry->add('outbox', function (array $options) use ($connectionRegistry, $producerMessageRepository) {
        return new OutboxKafkaConnection(
            producerMessageRepository: $producerMessageRepository,
            connectionRegistry: $connectionRegistry,
            sourceConnectionName: $options['connection_for']
        );
    });

    $message = MessageBuilder::for($topicRegistry)
        ->build([
            'payload' => 'test-message',
            'topic_name' => 'products',
            'headers' => ['foo' => 'bar'],
        ]);

    $connectionFaker = new ConnectionFaker();
    $connectionFaker->addMessage($message);

    $driverRegistry->add('faker', function () use ($connectionFaker) {
        return $connectionFaker;
    });

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', VoidConsumerHandlerFaker::class))
            )
        );

    $bus = new Bus(
        new Bus\ThreadRegistry(
            $connectionRegistry,
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new PipelineFactory(new NativeResolver())),
                $topicRegistry
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new ConsumerMessageHandlerFactory(
                        new PipelineFactory(new NativeResolver()),
                        new ConsumerRouterFactory(
                            new NativeResolver(),
                            new PipelineFactory(new NativeResolver()),
                            $topicRegistry
                        )
                    )
                ),
                $workerRegistry
            )
        ),
        'outbox'
    );

    $bus->createListener('default-listener')
        ->listen();

    expect($connectionFaker->committedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->committedMessages['production.fact.products.1'][0]->original())
        ->toHaveProperty('payload', 'test-message')
        ->toHaveProperty('headers', ['foo' => 'bar']);
});
