<?php


namespace App\Http\Services\Message;

use App\Exceptions\GSMConnectionNotFoundException;
use Exception;
use App\Models\Setting;
use PhpParser\Node\Stmt\TryCatch;
use PHPUnit\Runner\Extension\Extension;

class GSMConnection
{
    private static $gsmConnectionInstance = null;
    private $debug;
    private $port;
    private $baud;
    public $fp;
    protected $buffer;
    private $strReply = "";

    private function __construct()
    {
        $setting = Setting::first();

        if(!$setting){
            throw new GSMConnectionNotFoundException('تنظیمات پورت یا باند مشخص نشده است.');
        }

        $this->port = $setting->port;
        $this->baud = $setting->baud_rate;
        $this->gsmConnection();
    }

    public static function getGSMConnectionInstance()
    {

        if (self::$gsmConnectionInstance == null) {
            self::$gsmConnectionInstance = new GSMConnection();
        }

        return self::$gsmConnectionInstance;
    }

    private function gsmConnection()
    {

        try {
            exec("MODE {$this->port}: BAUD={$this->baud} PARITY=N DATA=8 STOP=1", $output, $retval);
            if ($retval != 0) {
                throw new GSMConnectionNotFoundException('امکان اتصال به مودم فراهم نیست ، لطفا پورت را چک کنید');
            }
        } catch (GSMConnectionNotFoundException $e) {
            throw $e;
            return back()->withError('امکان اتصال به مودم فراهم نیست ، لطفا پورت را چک کنید');
        }
    }

    public function sendATCommand($command, $delay = 2000000)
    {

        try {

            $this->fp = fopen($this->port, "r+");


            if (!$this->fp) {
                throw new GSMConnectionNotFoundException("اتصال به پورت امکان پذیر نیست", 500);
            }
        } catch (GSMConnectionNotFoundException $e) {
            error_log("GSM Connection Error: " . $e->getMessage());
            throw $e;
            //    return back()->withError('امکان اتصال به مودم فراهم نیست ، لطفا پورت را چک کنید');
        }


        fputs($this->fp, "$command\r");
        usleep($delay * 2);

        $response = '';
        usleep(500000); // Give some additional time before reading response
        while ($buffer = fgets($this->fp, 128)) {
            $response .= $buffer;
            if (strpos($buffer, "OK") !== false || strpos($buffer, "ERROR") !== false) {
                break;
            }
        }

        fclose($this->fp);
        return $response;
    }

    public function read()
    {
        //$this->strReply = $this->sendATCommand("AT+CMGL=\"REC UNREAD\"");
        $this->strReply = $this->sendATCommand("AT+CMGL=\"ALL\"");


        $arrMessages = explode("+CMGL:", $this->strReply);

        return $arrMessages;
    }

    public function send($tel, $message)
    {

        //Filter tel
        $tel = preg_replace("%[^0-9\+]%", '', $tel);

        //Filter message text
        $message = preg_replace("%[^\040-\176\r\n\t]%", '', $message);

        $status = $this->sendATCommand("AT+CMGF=1", 1000000); // Set to text mode before sending SMS

        if (!$status) {
            throw new Exception('Unable to set text mode');
        }
        $response = $this->sendATCommand("AT+CMGS=\"{$tel}\"", 1000000);  // 1 second delay


        echo "CMGS Command Response: $response\n";

        // If the response contains '>', it's ready to accept the message text
        if (strpos($response, '>') !== false) {
            // Send the message text
            $response = $this->sendATCommand($message, 2000000);  // 2 second delay for sending the message
            echo "Message Send Response: $response\n";

            // End message by sending CTRL+Z (ASCII code 26)
            $response = $this->sendATCommand(chr(26));  // Wait 5 seconds after sending CTRL+Z
            echo "End Message Response: $response\n";

            // Check if the message was sent successfully
            if (strpos($response, 'OK') !== false) {
                return true;  // Message sent successfully
            }
        }

        return false;  // Message sending failed


    }


    public function deleteMessage($index = null)
    {
        try {
            $this->sendATCommand("AT+CMGF=1", 1000000); // تنظیم به حالت متنی

            if ($index !== null) {
                // حذف پیام خاص
                $command = "AT+CMGD={$index}";
            } else {
                // حذف تمامی پیام‌ها
                $command = "AT+CMGD=1,4";
            }


            $this->sendATCommand("AT+CPMS=\"SM\"");

            // ارسال دستور
            $response = $this->sendATCommand($command, 1000000);
            error_log("Delete Response: " . $response);

            // بررسی موفقیت‌آمیز بودن عملیات
            if (strpos($response, 'OK') !== false) {
                return true;
            } else {
                throw new Exception('خطا در حذف پیام: ' . $response);
            }
        } catch (Exception $e) {
            error_log("Delete Message Error: " . $e->getMessage());
            throw $e;
        }
    }




    //Setup COM port
    // public function init()
    // {

    //     // $this->debugmsg("Setting up port: \"{$this->port} @ \"{$this->baud}\" baud");

    //     exec("MODE {$this->port}: BAUD={$this->baud} PARITY=N DATA=8 STOP=1", $output, $retval);

    //     if ($retval != 0) {
    //         throw new Exception('Unable to setup COM port, check it is correct');
    //     }

    //     $this->debugmsg(implode("\n", $output));

    //     $this->debugmsg("Opening port");

    //     //Open COM port
    //     $this->fp = fopen($this->port . ':', 'r+');

    //     //Check port opened
    //     if (!$this->fp) {
    //         throw new Exception("Unable to open port \"{$this->port}\"");
    //     }

    //     $this->debugmsg("Port opened");
    //     $this->debugmsg("Checking for responce from modem");

    //     //Check modem connected
    //     fputs($this->fp, "AT\r");

    //     //Wait for ok
    //     $status = $this->wait_reply("OK\r\n", 5);

    //     if (!$status) {
    //         throw new Exception('Did not receive responce from modem');
    //     }

    //     $this->debugmsg('Modem connected');

    //     //Set modem to SMS text mode
    //     $this->debugmsg('Setting text mode');
    //     fputs($this->fp, "AT+CMGF=1\r");

    //     $status = $this->wait_reply("OK\r\n", 5);

    //     if (!$status) {
    //         throw new Exception('Unable to set text mode');
    //     }

    //     $this->debugmsg('Text mode set');
    // }

    //Wait for reply from modem
    // protected function wait_reply($expected_result, $timeout)
    // {
    //     // clear reply cache
    //     $this->strReply = "";

    //     $this->debugmsg("Waiting {$timeout} seconds for expected result");

    //     //Clear buffer
    //     $this->buffer = '';

    //     //Set timeout
    //     $timeoutat = time() + $timeout;

    //     //Loop until timeout reached (or expected result found)
    //     do {

    //         // $this->debugmsg('Now: ' . time() . ", Timeout at: {$timeoutat}");

    //         $buffer = fread($this->fp, 1024);
    //         $this->buffer .= $buffer;

    //         usleep(200000); //0.2 sec
    //         // $this->debugmsg("Received: {$buffer}");

    //         $strReply = "";
    //         // get response

    //         $strReply          = $buffer;
    //         $this->strReply    .= $strReply;


    //         //Check if received expected responce
    //         if (preg_match('/' . preg_quote($expected_result, '/') . '$/', $this->buffer)) {
    //             $this->debugmsg('Found match');
    //             return true;
    //             //break;
    //         } else if (preg_match('/\+CMS ERROR\:\ \d{1,3}\r\n$/', $this->buffer)) {
    //             return false;
    //         }
    //     } while ($timeoutat > time());

    //     // $this->debugmsg('Timed out');

    //     return false;
    // }

    //Print debug messages
    // protected function debugmsg($message)
    // {

    //     if ($this->debug == true) {
    //         $message = preg_replace("%[^\040-\176\n\t]%", '', $message);
    //         echo $message . "\n";
    //     }
    // }

    //Close port
    // private function close()
    // {

    //     $this->debugmsg('Closing port');

    //     fclose($this->fp);
    // }
}
