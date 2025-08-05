<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Datalogger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Services\Message\GSMConnection;
use Illuminate\Support\Facades\Log;

class OrderCodeEveryTenMinutes implements ShouldQueue
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


        $now = Carbon::now();
        $dataloggers = Datalogger::whereHas('order_codes', function ($query) use ($now) {

            $query->where('datalogger_order_code.time', '10')
                ->where(function ($q) use ($now) {
                    $q->where('datalogger_order_code.last_sent_at', '<=', $now->subMinutes(10))
                        ->orWhereNull('datalogger_order_code.last_sent_at');
                })
                ->where('datalogger_order_code.status', '1');
        })->get();

        foreach ($dataloggers as $datalogger) {
            foreach ($datalogger->order_codes as $order_code) {

                try {
                    $gsmConnection = app(GSMConnection::class);
                    $gsmConnection->send($datalogger->mobile_number, $order_code->name);
                } catch (\Throwable $e) {
                    Log::error("GSM Send Error: " . $e->getMessage());
                }


                // Update the pivot table last_sent_at to current time after processing
                $datalogger->order_codes()->updateExistingPivot($order_code->id, ['last_sent_at' => $now]);
            }
        }
    }
}
