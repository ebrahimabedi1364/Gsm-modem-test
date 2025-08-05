<?php

namespace App\Http\Requests;

use App\Rules\uniqueDataloggerOrderCode;
use Illuminate\Foundation\Http\FormRequest;

class DataloggerOrdercodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $device=$this->route('device');
        if($this->isMethod('post')){
            return [
                'order_code_id'   => ['required', 'exists:order_codes,id', new uniqueDataloggerOrderCode($device->id) ],
                'time'   => ['required','in:60,15,30,10,0'],
    
            ];

        }
        else{
            $order_code=$this->route('orderCode');
            return [
                'order_code_id'   => ['required', 'exists:order_codes,id', new uniqueDataloggerOrderCode($device->id,$order_code->id) ],
                'time'   => ['required','in:60,15,30,10,0'],
    
            ];

        }
        
    }
}
