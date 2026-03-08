<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'medecin';
    case Admin = 'admin';
}
