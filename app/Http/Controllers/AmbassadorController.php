<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AmbassadorController extends Controller
{
    /**
     * Get all the ambassadors using a local scope method ambassador()
     */
    public function index()
    {
        return User::ambassadors()->get();
    }
}
