<?php

namespace App\Enum;

enum MessageStatus: string
{
    case READ = 'read';
    case SENT = 'sent';
}
