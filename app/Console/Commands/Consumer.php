<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RdKafka;
use App\Core\Models\Kafka;

class Consumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receive message from kafka topic';

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
        $conf = new RdKafka\Conf();

        // Set the group id. This is required when storing offsets on the broker
        $conf->set('group.id', 'myConsumerGroup');

        $rk = new RdKafka\Consumer($conf);
        $rk->addBrokers(config("kafka.brokers"));

        $topicConf = new RdKafka\TopicConf();
        $topicConf->set('auto.commit.interval.ms', 100);

        // Set the offset store method to 'file'
        $topicConf->set('offset.store.method', 'file');
        $topicConf->set('offset.store.path', sys_get_temp_dir());

        // Alternatively, set the offset store method to 'broker'
        // $topicConf->set('offset.store.method', 'broker');

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');

        $topic = $rk->newTopic(Kafka::TOPIC_DEFAULT, $topicConf);

        // Start consuming partition 0
        $topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

        while (true) {
            $message = $topic->consume(0, 120*10000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    echo sprintf("Received topic=[%s], message=[%s], offset=[%s]", $message->topic_name, $message->payload, $message->offset)."\n";
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }
}
