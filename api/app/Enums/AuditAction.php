<?php

namespace App\Enums;

enum AuditAction: string
{
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case FORGET_PASSWORD = 'forget password';
    case RESET_PASSWORD = 'reset password';

    case REQUEST_ACCESS = 'request access';
    case REQUEST_APPROVE = 'request approve';
    case REQUEST_REJECT = 'request reject';

    case SESSION_START = 'session start';
    case SESSION_END = 'session end';
    case SESSION_EXPIRE = 'session expire';
    case SESSION_TERMINATE = 'session terminate';
}
