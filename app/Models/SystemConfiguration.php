<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group_name',
        'is_editable',
    ];

    protected function casts(): array
    {
        return [
            'is_editable' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            return $default;
        }

        return match ($config->type) {
            'number' => (int) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    public static function setValue(string $key, mixed $value): void
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            return;
        }

        $stringValue = is_array($value) ? json_encode($value) : (string) $value;
        $config->update(['value' => $stringValue]);
    }
}
