<?php

namespace App\Http\Controllers;

use App\Events\TestEvent;
use Illuminate\Http\Request;

class WebsocketController extends Controller
{
    public function test(Request $request){
        broadcast(new TestEvent('ciao a tutti se lo vedi vuol dire che sei stato bravo', $request->user()));
    }
}
