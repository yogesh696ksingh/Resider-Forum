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
                $all_complaints = DB::table('complaint')->where('user_id','1')->get();
        echo json_encode($all_complaints);
            }
    }

    public function searchpost(Request $request) {
        $user_id = 1;
        $all_complaints = DB::table('complaint')->where('user_id',$user_id)->get();
        echo json_encode($all_complaints);
    }    
    
}
