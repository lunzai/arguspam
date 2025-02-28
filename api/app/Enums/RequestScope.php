<?php

namespace App\Enums;

enum RequestScope: string
{
    case READ_ONLY = 'ReadOnly';
    case READ_WRITE = 'ReadWrite';
    case DDL = 'DDL';
    case DML = 'DML';
    case ALL = 'All';
}
