<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function checkdb() {
        if(DB::connection()->getDatabaseName())
            {
                echo "connected successfully to database ".DB::connection()->getDatabaseName();
            }
    }

    
    
}
