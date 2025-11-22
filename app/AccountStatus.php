<?php

namespace App;

enum AccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';
    case Unverified = 'unverified';
}
