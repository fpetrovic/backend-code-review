<?php

namespace App\Enum;

enum MessageStatus: string
{
    case Read = 'read';
    case Sent = 'sent';
}
