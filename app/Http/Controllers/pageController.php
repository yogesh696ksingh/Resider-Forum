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
        $query = DB::table('complaint');
        $page_data = $request->all();
        $user_id = $page_data['user_id'];
        $user_type = $page_data['user_type']; 
        $search_mat = $page_data && $page_data['searchByField'] ? $page_data['searchByField'] :  [];
        $offset = $page_data && $page_data['pageNumber'] ? $page_data['pageNumber']-1 : 0;
        $limit = $page_data && $page_data['count'] ? $page_data['count'] : 5;
        foreach ($search_mat as $key => $value) {
            if ($value["fieldId"] == 'freetext') {
                $all_complaints = $query->where('title','like','%'.$value['value'].'%')->orWhere('description','like','%'.$value['value'].'%');
            }
            if ($value["fieldId"] == 'location') {
                $all_complaints = $query->where('location_id','=', $value['value']);
            }
            if ($value["fieldId"] == 'status') {
                $all_complaints = $query->where('status',$value['value']);
            }
            if ($value["fieldId"] == 'user' && $value['value']=='mine') {
                if($user_type == 0) {
                    $all_complaints = $query->where('user_id',$user_id); /*session: user_id*/
                }
                else {
                    $all_complaints = $query->where('authority_id',$user_id);
                }
            }
            
        }
      
        $all_complaints = $query->offset($offset)->limit($limit)->get();

        $plucked_user_id = $all_complaints->pluck('user_id');
        $plucked_location_id = $all_complaints->pluck('location_id');
        
        $plucked_user_id = array_unique($plucked_user_id->all());
        $plucked_location_id = array_unique($plucked_location_id->all());
        
        $all_user_info = DB::table('users')->select('id','name')->whereIn('id',$plucked_user_id)->get();
        $all_location_info = DB::table('location')->select('id','area')->whereIn('id',$plucked_location_id)->get();
        
        $all_complaints = json_decode(json_encode($all_complaints),true);
        
        $user_info = $all_user_info->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        });
        $location_info = $all_location_info->mapWithKeys(function ($item) {
            return [$item->id => $item->area];
        });

        $user_info = $user_info->all();
        $location_info = $location_info->all();

        $user_info = json_decode(json_encode($user_info),true);
        $location_info = json_decode(json_encode($location_info),true);

        

        foreach ($all_complaints as $key => $value) {
            $all_complaints[$key]['username'] = $user_info[$value['user_id']];
            $all_complaints[$key]['area'] = $location_info[$value['location_id']];
            $all_complaints[$key]['short_desc'] = substr($value['description'], 0 , 100).' ...';
        }

        $all_complaints_count = $query->count();
        $result = [];
        $result['totalCount'] = $all_complaints_count;
        $result['data'] = $all_complaints;
        echo json_encode($result);
    }

    public function fetchlocation(Request $request)
    {
        $data = $request->all();
        $user_id = 1; /*session user id*/
        $query = DB::table('location');
        foreach ($data as $key => $value) {
            if ($value["fieldId"] == 'location') {
                $all_location = $query->where('area','like', '%'.$value['value'].'%');
            }
        }
        $all_location = $query->limit(10)->get();
        echo json_encode($all_location);
    }
    
    public function fetchauthority(Request $request)
    {
        $data = $request->all();
        $user_id = 1;
        $query = DB::table('users');
        foreach ($data as $key => $value) {
            if ($value["fieldId"] == 'location') {
                $authority = $query->where('auth_loc','=', $value['value'])->where('user_type','1');
            }
        }
        $authority = $query->first();
        $recieved = DB::table('complaint')->where('authority_id',$authority->id)->count();
        $responded = DB::table('complaint')->where([['authority_id',$authority->id],['status','!=',0]])->count();
        $authority->recieved = $recieved;
        $authority->responded = $responded;
        echo json_encode($authority);
    }

    public function fetchuser()
    {
        $user = DB::table('users')->where('id',1)->first();
        $reported = DB::table('complaint')->where('user_id',$user->id)->count();
        $completed = DB::table('complaint')->where([['user_id',$user->id],['status',2]])->count();
        $user->reported = $reported;
        $user->completed = $completed;
        echo json_encode($user);
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $user = DB::table('users')->where('email', $data['email'])->where('password', $data['password'])->first();
        if(!empty($user)) {
            if ($user->user_type == 0) {
                $reported = DB::table('complaint')->where('user_id',$user->id)->count();
                $completed = DB::table('complaint')->where([['user_id',$user->id],['status',2]])->count();
                $user->reported = $reported;
                $user->completed = $completed;
                $con_auth = DB::table('users')->where('auth_loc',$user->auth_loc)->where('user_type',1)->first();
                if(!empty($con_auth)) {
                    $con_auth->recieved = DB::table('complaint')->where('authority_id',$con_auth->id)->count();
                    $con_auth->responded = DB::table('complaint')->where([['authority_id',$con_auth->id],['status','!=',0]])->count();
                    $user->authority = $con_auth;
                }
                echo json_encode($user);
            }
            elseif ($user->user_type == 1) {
                $recieved = DB::table('complaint')->where('authority_id',$user->id)->count();
                $responded = DB::table('complaint')->where([['authority_id',$user->id],['status',2]])->count();
                $user->recieved = $recieved;
                $user->responded = $responded;
                $user_total = DB::table('users')->where('user_type',0)->count();
                $authority_total = DB::table('users')->where('user_type',1)->count();
                $user->user_total = $user_total;
                $user->authority_total = $authority_total;
                DB::table('complaint')->where('authority_id', $user->id)->update(['status' => 1]);
                echo json_encode($user);   
            }
        }
        else {
            echo '{}';
        }
        
    }

    public function reportuser(Request $request)
    {
        $user_id = 1;
        $data = $request->all();
        $pincode = 410210;
        $city = "Navi Mumbai";
        $state = "Maharashtra";
        $loc_id = 1;
        $aut_id = DB::table('users')->select('id')->where("auth_loc", $loc_id)->first();
        DB::table('complaint')->insert(
            ['user_id' => $user_id, 'authority_id' => $aut_id, 'title' => $data["problemTitle"], 'location_id' => $loc_id, 'description' => $data["problemDescription"]]
        );
    }

    public function changestatus(Request $request)
    {
        $data = $request->all();
        DB::table('complaint')->where('id', $data["id"])->update(['status' => $data["status"]]);
        echo $data["status"];
    }
}
