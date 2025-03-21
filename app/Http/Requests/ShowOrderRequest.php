<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ShowOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
        public function authorize()
        {
            $order =  Order::find($this->route('order'));
            $user = $this->user();

            if ($user->hasPermission('order.show') || $user->id === $order->user_id) {
                return true;
            } else {
                return false;
            }

        }
    public function rules()
    {
        return [
        //
        ];
    }
}
