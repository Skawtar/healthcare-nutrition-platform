<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // This line is CRUCIAL

class Controller extends BaseController // This line is CRUCIAL
{
    use AuthorizesRequests, ValidatesRequests;
}