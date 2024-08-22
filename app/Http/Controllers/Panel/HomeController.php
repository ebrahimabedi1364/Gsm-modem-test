<?php

namespace App\Http\Controllers\Panel;

use Carbon\Carbon;
use App\Models\Message;
use App\Models\Setting;
use App\Models\CheckCode;
use App\Models\Datalogger;
use App\Jobs\RecieveMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Http\Services\Message\GSMConnection;
use App\Http\Services\Message\DestroyMessage;
use App\Http\Services\Message\RecieveMessageService;

class HomeController extends Controller
{
	

	public function index()
	{
<<<<<<< HEAD
		
=======

		

>>>>>>> 7236aee05ab43b09269d845dae10fe3a52beeb13
		$dataloggers = Datalogger::all();
		return view('app.index', compact('dataloggers'));
	}

	public function readMessage(GSMConnection $Connection)
	{

		
		$arrMessages = $Connection->read();

		$strJunk = array_shift($arrMessages);

		// set return array
		$arrReturn = array();

		// check that we have messages
		if (is_array($arrMessages) && !empty($arrMessages)) {
			// for each message
			foreach ($arrMessages as $arrMessage) {
				// split content from metta data
				$arrMessage	= explode("\n", $arrMessage, 2);

				$strMetta	= trim($arrMessage[0]);
				$arrMetta	= explode(",", $strMetta);

				// var_dump($strtoarray);

				// if ($length > 1) {

				// 	for ($i = 0; $i < $length; $i + 2) {

				// 		$messageArray1[$strtoarray[$i]] = $strtoarray[$i + 1];
				// 	}
				// } else {
				// 	$messageArray1[$strtoarray[0]] = null;
				// }



				// set the message array to go in the return array
				$arrReturnMessage = array();

				// set the message values to return
				$arrReturnMessage['Id']			= trim($arrMetta[0], "\"");
				$arrReturnMessage['Status']		= trim($arrMetta[1], "\"");
				$arrReturnMessage['From']		= str_replace('+98', '0', trim($arrMetta[2], "\""));
				$arrReturnMessage['Date']		= trim($arrMetta[4], "\"");
				$arrTime						= explode("+", $arrMetta[5], 2);
				$arrReturnMessage['Time']		= trim($arrTime[0], "\"");




				// add message to return array
				$arrReturn[] = $arrReturnMessage;

				$datalogger = Datalogger::where('mobile_number', $arrReturnMessage['From'])->first();

				if ($datalogger) {

					$strContent	= trim($arrMessage[1]);
					$strtoarray = preg_split('/[\s]+/', trim($strContent));

					$messageArray1 = [];
					$length = count($strtoarray);

					for ($i = 0; $i < $length-1; $i += 2) {

						$messageArray1[$strtoarray[$i]] = $strtoarray[$i + 1];
					}



					Message::create([
						'from'    => $datalogger->mobile_number,
						'datalogger_id' => $datalogger->id,
						'time'    => $arrReturnMessage['Date'] . ' ' . $arrReturnMessage['Time'],
						'content' => $messageArray1,
						'type'    => '1'
					]);
				}
			}
		}

		// $deleteMessage=new DestroyMessage();
		// $deleteMessage->destroy();

		// Artisan::command('auto:recieveMessage');
		// $config=Setting::all();
		// $recieveMessage=new RecieveMessageService();
		// $recieveMessage->debug=true;
		// $recieveMessage->port=$config->port;
		// $recieveMessage->baud=$config->baud;
		// $recieveMessage->init();

		// $recieveMessage->read();
		// $strJunk = array_shift($arrMessages);

		// // set return array
		// $arrReturn = Array();

		// // check that we have messages
		// if (is_array($arrMessages) && !empty($arrMessages))
		// {
		// 	// for each message
		// 	foreach($arrMessages AS $strMessage)
		// 	{
		// 		// split content from metta data
		// 		$arrMessage	= explode("\n", $strMessage, 2);
		// 		$strMetta	= trim($arrMessage[0]);
		// 		$arrMetta	= explode(",", $strMetta);
		// 		$strContent	= trim($arrMessage[1]);


		// 		// set the message array to go in the return array
		// 		$arrReturnMessage = Array();

		// 		// set the message values to return
		// 		$arrReturnMessage['Id']			= trim($arrMetta[0], "\"");
		// 		$arrReturnMessage['Status']		= trim($arrMetta[1], "\"");
		// 		$arrReturnMessage['From']		= trim($arrMetta[2], "\"");
		// 		$arrReturnMessage['Date']		= trim($arrMetta[4], "\"");
		// 		$arrTime						= explode("+", $arrMetta[5], 2);
		// 		$arrReturnMessage['Time']		= trim($arrTime[0], "\"");
		// 		$arrReturnMessage['Content']	= trim($strContent);



		// 		// add message to return array
		// 		$arrReturn[] = $arrReturnMessage;

		//         $result=Message::create([
		//             'from'    => $arrReturnMessage['From'],
		//             'date'    => $arrReturnMessage['Date'],
		//             'time'    => $arrReturnMessage['Time'],
		//             'content' => $arrReturnMessage['Content'],
		//             'type'    => '0'
		//         ]);
		// 	}





		//}
		return redirect()->route('app.index')->with('swal-success', 'بروزرسانی با موفقیت انجام شد');
	}
}
