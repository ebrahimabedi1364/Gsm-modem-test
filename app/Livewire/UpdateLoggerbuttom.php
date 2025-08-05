<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\Datalogger;
use App\Http\Services\Message\GSMConnection;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Log;


class UpdateLoggerbuttom extends Component
{
    use LivewireAlert;

    public function updateLogger(GSMConnection $Connection)
    {



        $arrMessages = $Connection->read();

        // dd($arrMessages);
    

        try {
            $Connection->deleteMessage(); // حذف پیام از مودم
        } catch (\Exception $e) {
            Log::error('خطا در حذف پیام از مودم', ['error' => $e->getMessage()]);
            // ادامه اجرای برنامه بدون توقف
        }

        $strJunk = array_shift($arrMessages);


        // set return array
        $arrReturn = array();



        // check that we have messages
        if (is_array($arrMessages) && !empty($arrMessages)) {
            // for each message
            foreach ($arrMessages as $arrMessage) {
                // split content from metta data
                $arrMessage    = explode("\n", $arrMessage, 2);

                if (count($arrMessage) == 2) {
                    $strMetta    = trim($arrMessage[0]);
                    $arrMetta    = explode(",", $strMetta);

                    // set the message array to go in the return array
                    $arrReturnMessage = array();

                    // set the message values to return
                    $arrReturnMessage['Id']            = trim($arrMetta[0], "\"");
                    $arrReturnMessage['Status']        = trim($arrMetta[1], "\"");
                    $arrReturnMessage['From']        = str_replace('+98', '0', trim($arrMetta[2], "\""));
                    $arrReturnMessage['Date']        = trim($arrMetta[4], "\"");
                    $arrTime                        = explode("+", $arrMetta[5], 2);
                    $arrReturnMessage['Time']        = trim($arrTime[0], "\"");

                    // add message to return array
                    $arrReturn[] = $arrReturnMessage;

                    $datalogger = Datalogger::where('mobile_number', $arrReturnMessage['From'])->first();

                    if ($datalogger) {


                        $strContent    = trim($arrMessage[1]);
                        $messageArray1 = [];


                        // dd($strContent);
                        $messageArray1 = $datalogger->parseMessage($strContent);
                        

                        $lastStatus = $datalogger->last_status ?? [];



                        // بررسی و به‌روزرسانی وضعیت‌ها
                        foreach ($datalogger->checkCodes as $checkCode) {
                            if (isset($messageArray1[$checkCode->name]) && (!isset($lastStatus[$checkCode->name]) || $lastStatus[$checkCode->name] !== $messageArray1[$checkCode->name])) {
                                // فقط در صورتی که تغییر وجود داشته باشد، به‌روزرسانی می‌شود
                                $lastStatus[$checkCode->name] = $messageArray1[$checkCode->name];
                            }
                        }

                        if ($lastStatus) {
                            // به‌روزرسانی آخرین وضعیت و زمان آخرین به‌روزرسانی
                            $datalogger->lastCheckStatus = $lastStatus;

                            $datalogger->save();
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
        }

        $this->alert('success', 'بروزرسانی با موفقیت انجام شد');

        // return redirect()->route('app.index')->with('swal-success', 'بروزرسانی با موفقیت انجام شد');
    }
    public function render()
    {
        return view('livewire.update-loggerbuttom');
    }
}
