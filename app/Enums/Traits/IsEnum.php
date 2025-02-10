<?php

namespace App\Enums\Traits;

use ValueError;

trait IsEnum
{
    public static function casesValues(): array
    {
        return array_map(function ($enum) {
            return $enum->value;
        }, self::cases());
    }

    public static function valuesAndLabels(): array
    {
        $values_and_labels = [];
        foreach (self::cases() as $status) {
            $values_and_labels[$status->value] = $status->label();
        }

        return $values_and_labels;
    }

    public static function getLabelFromString(string $name): string
    {
        foreach (self::cases() as $status) {
            if ($name === $status->value) {
                return $status->label();
            }
        }
        throw new ValueError("{$name} is not a valid backing value for enum ".self::class);
    }
}
