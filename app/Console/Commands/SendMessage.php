<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RdKafka;

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
        $rk = new RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("kafka1:9092");

        $topic = $rk->newTopic("testTopic");        
    }
}
