<?php

namespace App\Enums\Accommodation;

enum BedType: string
{
    case Double = 'double';
    case King = 'king';
    case Queen = 'queen';
    case Single = 'single';
    case Twin = 'twin';
    case Bunk = 'bunk';
    case Sofa = 'sofa';
    case Futon = 'futon';

    public function label(): string
    {
        return __('enums/bed_type.'.$this->value);
    }

    public function description(): string
    {
        return __('enums/bed_type.'.$this->value.'_description');
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->label(),
            'description' => $this->description(),
        ];
    }

    public static function options(): array
    {
        return array_map(fn (self $case) => $case->toArray(), self::cases());
    }
}
