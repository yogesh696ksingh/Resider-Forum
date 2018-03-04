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

    public function searchpost(Request $request) {
        $user_id = 1; /*session: user_id*/
        $query = DB::table('complaint');
        $search_mat = $request->all();
        foreach ($search_mat as $key => $value) {
            if ($value["field_id"] == 'freetext') {
                $all_complaints = $query->where('title','like','%'.$value['value'].'%')->orWhere('description','like','%'.$value['value'].'%');
            }
            if ($value["field_id"] == 'location') {
                $all_complaints = $query->where('location_id','=', $value['value']);
            }
            if ($value["field_id"] == 'status') {
                $all_complaints = $query->where('status',$value['value']);
            }
            if ($value["field_id"] == 'user' && $value['value']=='mine') {
                $all_complaints = $query->where('user_id',$user_id); /*session: user_id*/
            }
            
        }
      
        $all_complaints = $query->get();
        $plucked_user_id = $all_complaints->pluck('user_id');
        $plucked_user_id = array_unique($plucked_user_id->all());
        $all_user_info = DB::table('users')->select('id','name')->whereIn('id',$plucked_user_id)->get();
        $all_complaints = json_decode(json_encode($all_complaints),true);
        $user_info = $all_user_info->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        });
        $user_info = $user_info->all();
        $user_info = json_decode(json_encode($user_info),true);
        foreach ($all_complaints as $key => $value) {
            $all_complaints[$key]['username'] = $user_info[$value['user_id']];
        }
        $all_complaints_count = DB::table('complaint')->where('user_id',$user_id)->count();
        echo json_encode($all_complaints);
    }    
    

}
