<?php

namespace App\Support\Enum\Saldo;

enum Status: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('В очереди'),
            self::PROCESSING => __('Обработка'),
            self::COMPLETED => __('Завершено'),
            self::FAILED => __('Ошибка'),
        };
    }

    public function canDelete(): bool
    {
        return match ($this) {
            self::PENDING, self::FAILED => true,
            default => false,
        };
    }
}
