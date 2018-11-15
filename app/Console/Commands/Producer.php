<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RdKafka;
use App\Core\Models\Kafka;
use ProtoMsg\Location;
use ProtoMsg\EventEnvelope;

class Producer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:producer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to kafka topic';

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
        // serialize
        $location = new Location();
        $location->setLat(22.28552);
        $location->setLng(114.15769);

        $event = new EventEnvelope();
        $event->setTs(time());
        $event->setLocation($location);
        $data = $event->serializeToString();

        $rk = new RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers(config("kafka.brokers"));

        $topic = $rk->newTopic(Kafka::TOPIC_DEFAULT);  
        $topic->produce(RD_KAFKA_PARTITION_UA, 0 , $data);
        $rk->poll(0);
        
        while ($rk->getOutQLen() > 0) {
            $rk->poll(50);
        }  
        echo "send message is done\n";
    }
}
