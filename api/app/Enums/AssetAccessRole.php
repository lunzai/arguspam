<?php

namespace App\Enums;

enum AssetAccessRole: string
{
    case REQUESTER = 'requester';
    case APPROVER = 'approver';
    case AUDITOR = 'auditor';
}
