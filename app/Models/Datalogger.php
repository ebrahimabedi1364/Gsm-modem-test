<?php

namespace App\Models;

use App\Models\Message;
use App\Models\CheckCode;
use App\Models\OrderCode;
use PhpParser\Node\Stmt\Switch_;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\ForUserIndustrialCityScope;

class Datalogger extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dataloggers';

    protected $guarded = ['id'];

    // protected $casts = ['content' => array()];
    protected $casts = [
        'lastCheckStatus' => 'array',
    ];


    
    protected static function booted()
    {
        static::addGlobalScope(new ForUserIndustrialCityScope);
    }

    public function dataloggerable()
    {
        return $this->morphTo();
    }


    public function getDataLoggerTypeAttribute()
    {
        switch ($this->dataloggerable_type) {
            case 'App\Models\Well':
                $type = 'چاه';
                break;
            case 'App\Models\Pump':
                $type = 'پمپ';
                break;
            case 'App\Models\Source':
                $type = 'منبع';
                break;
            default:
        }
        return $type;
    }


    public function checkCodes()
    {
        return $this->belongsToMany(CheckCode::class);
    }

    public function industrialCity()
    {
        return $this->belongsTo(IndustrialCity::class);
    }

    public function getDeviceSahpeAttribute()
    {
        switch ($this->dataloggerable_type) {
            case 'App\Models\Well':
                $type = 'well';
                break;
            case 'App\Models\Pump':
                $type = 'pump';
                break;
            case 'App\Models\Source':
                $type = 'source';
                break;
            default:
        }
        return $type;
    }


    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastRecieveMessage()
    {
        return $this->messages()->where('type', '1')->latest('time', 'created_at')->first();
    }

    public function powerCheckCode()
    {
        return $this->belongsTo(CheckCode::class, 'power');
    }

    public function dataloggerLastStatus()
    {
        //$lastMessage = $this->lastRecieveMessage();
        $powerCheckCodeName = $this->powerCheckCode->name ?? null;

        if ($powerCheckCodeName) {
            return $this->lastCheckStatus[$powerCheckCodeName] ?? 'NoConnect';
        }

        return 'NoConnect';
    }

    public function sourceVolumePercentage()
    {
        // if ($this->dataloggerLastStatus() == 'ON') {


        //     $currentHeight = str_replace('meter', '', $this->lastRecieveMessage()->content['Height']);
        //     $baseHeight = $this->fount_height;
        //     $persent = round(($currentHeight / $baseHeight) * 100, 0);
        // } else {
        //     $persent = 0;
        // }
        //dd(  $this->lastRecieveMessage());


        //$lastMessage = $this->lastRecieveMessage();



        return $this->lastCheckStatus['level']  ??  'NnConnect';


        //return 'NoConnect';

        // $baseHeight = $this->fount_height ?? '';

        // $persent = (($currentHeight / $baseHeight) * 100);

        //return $currentHeight;
    }




    public function order_codes()
    {
        return $this->belongsToMany(OrderCode::class)->withPivot(['time', 'last_sent_at','status']);
    }


    public function parseMessage($message)
    {

        switch (class_basename($this->dataloggerable_type)) {

            case 'Source':
                $pattern = '/^(.+?)[\s\-:]+(.+)$/'; // برای نوع 2
                $unitRemovals = ['bar', 'meter', '%', 'm3/s'];
                break;

            default:
                $delimiter = '/[\s]+/'; // پیش‌فرض
                $unitRemovals = [];
                break;
        }

        // تقسیم پیام به خطوط
        $lines = explode("\n", trim($message));

        // آرایه برای ذخیره جفت‌های کلید-مقدار
        $messageArray = [];

        foreach ($lines as $line) {
            $line = trim($line); // حذف فاصله‌های اضافی

            if ($line === '') continue; // عبور از خطوط خالی

            if (class_basename($this->dataloggerable_type) == 'Source') {
                // استفاده از preg_match برای نوع 2
                if (preg_match($pattern, $line, $matches)) {
                    $key = strtolower(trim($matches[1]));
                    $value = trim($matches[2]);

                    // حذف واحدهای اضافی از مقدار
                    foreach ($unitRemovals as $unit) {
                        $value = str_replace($unit, '', $value);
                    }

                    $messageArray[$key] = $value;
                }
            } else {
                // استفاده از preg_split برای سایر انواع
                $strtoarray = preg_split($delimiter, $line);
                
                if (count($strtoarray) >= 2) {
                    $key = strtolower(trim($strtoarray[0]));
                    $value = trim($strtoarray[1]);

                    // حذف واحدها (در صورت وجود)
                    foreach ($unitRemovals as $unit) {
                        $value = str_replace($unit, '', $value);
                    }

                    $messageArray[$key] = $value;
                }
            }
        }
       

        return $messageArray;
    }
}
