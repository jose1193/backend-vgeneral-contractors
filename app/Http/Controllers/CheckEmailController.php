<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controller as BaseController;

class CheckEmailController extends BaseController
{

    public function checkEmailAvailability($email)
    {
         $exists = User::where('email', $email)->exists();

    return response()->json([
        'available' => !$exists,
        'message' => $exists ? 'Email is already taken' : 'Email is available'
    ]);

    }
    
   

}

