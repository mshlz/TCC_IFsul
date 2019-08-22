<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function homepage(){
        return view('welcome');
    }

    public function dashboard(){
        return view('index');
    }

    #--------------------------------------------------------------------------
    // method to requester login VIEW
    #--------------------------------------------------------------------------

    public function login(){
        return view('login');
    }

    //=========================================================================

}
