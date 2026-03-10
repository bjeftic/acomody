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
        return match ($this) {
            self::Double => 'Double Bed',
            self::King => 'King Bed',
            self::Queen => 'Queen Bed',
            self::Single => 'Single Bed',
            self::Twin => 'Twin Bed',
            self::Bunk => 'Bunk Bed',
            self::Sofa => 'Sofa Bed',
            self::Futon => 'Futon Mat',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Double => '131–150 cm (52–59 inches) wide',
            self::King => '181–210 cm (71–82 inches) wide',
            self::Queen => '151–180 cm (60–70 inches) wide',
            self::Single => '90–130 cm (35–51 inches) wide',
            self::Twin => '90–130 cm (35–51 inches) wide',
            self::Bunk => '',
            self::Sofa => '',
            self::Futon => '',
        };
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
