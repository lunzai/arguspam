<?php

namespace App\Enums;

enum DatabaseScope: string
{
    case READ_ONLY = 'ReadOnly';
    case READ_WRITE = 'ReadWrite';
    case DDL = 'DDL';
    case ALL = 'All';
}
