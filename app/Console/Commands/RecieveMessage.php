<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\Setting;
use App\Jobs\ConnectModem;
use App\Models\Datalogger;
use Illuminate\Console\Command;
use App\Jobs\RecieveMessage as JobsRecieveMessage;
use App\Http\Services\Message\RecieveMessageService;

class RecieveMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:recieveMessage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    public $connect;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        

        JobsRecieveMessage::dispatch();

<<<<<<< HEAD
=======

>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13
    }
}
