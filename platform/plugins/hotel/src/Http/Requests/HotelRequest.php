<?php

namespace Botble\Hotel\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class HotelRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
            'content' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:255',
            'image' => 'nullable|string',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
