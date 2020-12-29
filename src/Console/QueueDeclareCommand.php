<?php

namespace Intergo\RabbitQueue\Console;

use Exception;
use Illuminate\Console\Command;
use Intergo\RabbitQueue\Queue\Connectors\RabbitMQConnector;

class QueueDeclareCommand extends Command
{
    protected $signature = 'rabbitmq:queue-declare
                           {name : The name of the queue to declare}
                           {connection=rabbitmq : The name of the queue connection to use}
                           {--max-priority=4}
                           {--durable=1}
                           {--auto-delete=0}';

    protected $description = 'Declare queue';

    /**
     * @param RabbitMQConnector $connector
     * @throws Exception
     */
    public function handle(RabbitMQConnector $connector): void
    {
        $config = $this->laravel['config']->get('queue.connections.'.$this->argument('connection'));

        $queue = $connector->connect($config);

        if ($queue->isQueueExists($this->argument('name'))) {
            $this->warn('Queue already exists.');

            return;
        }
        $maxPriority = (int) $this->option('max-priority');
        $args = ['x-max-priority' => $maxPriority ?? 1];
        $queue->declareQueue(
            $this->argument('name'),
            (bool) $this->option('durable'),
            (bool) $this->option('auto-delete'),
            $args
        );

        $this->info('Queue declared successfully.');
    }
}
