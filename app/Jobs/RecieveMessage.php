<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Services\Message\GSMConnection;
<<<<<<< HEAD
=======
use App\Http\Services\Message\RecieveMessageService;
>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13


class RecieveMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       
<<<<<<< HEAD
        $gsmConnection = app(GSMConnection::class);
		$arrMessages = $gsmConnection->read();
=======
			
		$gsmConnection = app(GSMConnection::class);
        $arrMessages=$gsmConnection->read();
>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13
		
        $strJunk = array_shift($arrMessages);
		
		// set return array
		$arrReturn = Array();
		
		// check that we have messages
		if (is_array($arrMessages) && !empty($arrMessages))
		{
			// for each message
			foreach($arrMessages as $arrMessage)
			{
				// split content from metta data
				$arrMessage	= explode("\n", $arrMessage, 2);
				$strMetta	= trim($arrMessage[0]);
				$arrMetta	= explode(",", $strMetta);
				$strContent	= trim($arrMessage[1]);
				
				
				// set the message array to go in the return array
				$arrReturnMessage = Array();
				
				// set the message values to return
				$arrReturnMessage['Id']			= trim($arrMetta[0], "\"");
				$arrReturnMessage['Status']		= trim($arrMetta[1], "\"");
				$arrReturnMessage['From']		= trim($arrMetta[2], "\"");
				$arrReturnMessage['Date']		= trim($arrMetta[4], "\"");
				$arrTime						= explode("+", $arrMetta[5], 2);
				$arrReturnMessage['Time']		= trim($arrTime[0], "\"");
				$arrReturnMessage['Content']	= trim($strContent);
				
	
				
				// add message to return array
				$arrReturn[] = $arrReturnMessage;

                Message::create([
                    'from'    => $arrReturnMessage['From'],
                    // 'date'    => $arrReturnMessage['Date'],
                    'time'    => $arrReturnMessage['Date'].' '.$arrReturnMessage['Time'],
                    'content' => $arrReturnMessage['Content'],
                    'type'    => '1'
                ]);
			}
			
		}

    }
}
