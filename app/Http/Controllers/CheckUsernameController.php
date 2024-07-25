<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class CheckUsernameController extends BaseController
{
    public function checkUsernameAvailability($username)
    {
       

    $exists = User::where('username', $username)->exists();

    return response()->json([
        'available' => !$exists,
        'message' => $exists ? 'Username is already taken' : 'Username is available'
    ]);

    }
}
