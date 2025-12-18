<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_base' => $this->is_base,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'decimal_places' => $this->decimal_places,
            'symbol_position' => $this->symbol_position,
            'thousands_separator' => $this->thousands_separator,
            'decimal_separator' => $this->decimal_separator,
        ];
    }
}
