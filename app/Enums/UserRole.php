<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case BRANCH = 'branch';
    case ADMIN = 'admin';
}
