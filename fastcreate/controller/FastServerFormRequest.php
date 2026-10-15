<?php

namespace Pterodactyl\Http\Requests\Admin\Extensions\darkpsyho;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class FastServerFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'preset' => 'required|integer|between:1,8',
            'egg_id' => 'required|integer|exists:eggs,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|min:1|max:120',
            'quantity' => 'nullable|integer|min:1|max:20',
        ];
    }
}
