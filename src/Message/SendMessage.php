<?php

declare(strict_types=1);

namespace App\Message;

readonly class SendMessage
{
    public function __construct(
        public string $text,
    ) {
    }
}
