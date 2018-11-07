<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:send-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to kafka';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logger = new Logger('my_logger');
        $logger->pushHandler(new StreamHandler('storage/logs/test.log', Logger::DEBUG));

        $config = \Kafka\ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList('127.0.0.1:9092');
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(1);
        $config->setIsAsyn(false);
        $config->setProduceInterval(500);
        $producer = new \Kafka\Producer(
            function() {
                return [
                    [
                        'topic' => 'test',
                        'value' => 'test....message.',
                        'key' => 'testkey',
                    ],
                ];
            }
        );

        $producer->setLogger($logger);
        $producer->success(function($result) {
            print "at success";
            var_dump($result);
        });
        $producer->error(function($errorCode) {
            print "at error ".$errorCode;
                // var_dump($errorCode);
        });
        $producer->send(true);
        
    }
}
