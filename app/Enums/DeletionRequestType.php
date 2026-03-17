<?php

namespace App\Enums;

enum DeletionRequestType: string
{
    case UserAccount = 'user_account';
    case HostAccount = 'host_account';
    case Accommodation = 'accommodation';
}
