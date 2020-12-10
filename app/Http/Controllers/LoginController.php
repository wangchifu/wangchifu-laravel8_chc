<?php

namespace App\Http\Controllers;

use App\Models\SchoolApi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        $school_apis = SchoolApi::orderby('code')->get();
        $select_school = [];
        foreach($school_apis as $school_api){
            $select_school[$school_api->code] = $school_api->client_id;
        }
        $schools = config('chcschool.schools');
        $data = [
            'select_school'=>$select_school,
            'schools'=>$schools,
        ];
        return view('login',$data);
    }
    public function g_auth(Request $request)
    {
        $username = explode('@',$request->input('username'));
        $data = array("email"=>$username[0],"password"=>$request->input('password'));
        $data_string = json_encode($data);
        $ch = curl_init('https://school.chc.edu.tw/api/auth');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $obj = json_decode($result,true);

        if($obj['success']) {
            //非教職員，即跳開
            if($obj['kind'] != "教職員"){
                return back()->withErrors(['g_suite_error'=>['非教職員 GSuite 帳號']]);
            }

            //是否已有此帳號
            $user = User::where('edu_key',$obj['edu_key'])
                ->first();

            if(empty($user)){
                //無使用者，即建立使用者資料
                $att['uid'] = $obj['uid'];
                $att['edu_key'] = $obj['edu_key'];
                $att['name'] = $obj['name'];
                $att['email'] = $obj['email'];
                $att['kind'] = $obj['kind'];
                $att['title'] = $obj['title'];
                $att['code'] = $obj['code'];
                $att['school'] = $obj['school'];
                $att['schools'] = serialize($obj['schools']);//陣列序列化
                $att['username'] = $request->input('username');
                $att['password'] = bcrypt($request->input('password'));

                $att['username'] = $username[0];

                User::create($att);

            }else{
                //被停用了
                if($user->disable){
                    return back()->withErrors(['g_suite_error'=>['這個帳號被停用了']]);
                };

                //有此使用者，即更新使用者資料
                $att['name'] = $obj['name'];
                $att['kind'] = $obj['kind'];
                $att['title'] = $obj['title'];
                $att['code'] = $obj['code'];
                $att['school'] = $obj['school'];
                $att['schools'] = serialize($obj['schools']);//陣列序列化
                $att['username'] = $request->input('username');
                $att['password'] = bcrypt($request->input('password'));

                $user->update($att);
            }

            if(Auth::attempt(['edu_key' => $obj['edu_key'],
                'password' => $request->input('password'),'disable' => null])){
                return redirect()->route('index');
            }else{
                return back()->withErrors(['g_suite_error'=>['登入錯誤']]);
            }
        }else{
            return back()->withErrors(['g_suite_error'=>['GSuite認證錯誤']]);
        }

    }

    public function openid_get(Request $request)
    {
        $openid = $request->input('openid');//學校代碼-帳號
        $guid = $request->input('guid');//edu_key
        $name = $request->input('name');
        $unit = $request->input('unit');//學校代碼
        $title = $request->input('title');//教師或學生或....

        if($title == "學生"){
            $words = "僅限國中小學校教職員登入";
            $data = [
                'words'=>$words,
            ];
            return view('errors.others',$data);
        }else{

            //查是否有edu_key登入
            $user = User::where('edu_key',$guid)
                ->first();
            if(empty($user)){
                //無使用者，即建立使用者資料
                $att['edu_key'] = $guid;
                $att['name'] = $name;
                $att['code'] = $unit;
                $schools = config('chcschool.schools');
                $att['school'] = $schools[$unit];
                $user = User::create($att);

            }else{
                //被停用了
                if($user->disable){
                    $words = "這個帳號被停用了";
                    $data = [
                        'words'=>$words,
                    ];
                    return view('errors.others',$data);
                };

                //有此使用者，即更新使用者資料
                $att['name'] = $name;
                $att['code'] = $unit;
                $schools = config('chcschool.schools');
                $att['school'] = $schools[$unit];

                $user->update($att);
            }

            Auth::login($user);

            return redirect()->route('index');
        }

    }

    public function cloudschool_auth(Request $request)
    {
        $school_code = $request->input('school_code');
        $school_api = SchoolApi::where('code',$school_code)->first();
        $clientId = $school_api->client_id;
        $apiUrl = 'https://api.chc.edu.tw/school-oauth/authorize?client_id='.$clientId.'&response_type=code&state=abc';

        return redirect($apiUrl);
    }

    public function cloudschool_back(Request $request)
    {
        $data = json_decode($request->input('data'));

        if($data->role == "student"){
            $words = "僅限國中小學校教職員登入";
            $data = [
                'words'=>$words,
            ];
            return view('errors.others',$data);
        }else{

            //查是否有edu_key登入
            $user = User::where('edu_key',$data->edu_key)
                ->first();
            if(empty($user)){
                //無使用者，即建立使用者資料
                $att['edu_key'] = $data->edu_key;
                $att['name'] = $data->name;
                $att['code'] = $data->school_no;
                $schools = config('chcschool.schools');
                $att['school'] = $schools[$data->school_no];
                $att['title'] = $data->title_name;
                $att['role'] = $data->role;
                $att['title_kind'] = $data->title_kind;
                $att['group'] = $data->group;

                $user = User::create($att);

            }else{
                //被停用了
                if($user->disable){
                    $words = "這個帳號被停用了";
                    $data = [
                        'words'=>$words,
                    ];
                    return view('errors.others',$data);
                };

                //有此使用者，即更新使用者資料
                $att['name'] = $data->name;
                $att['code'] = $data->school_no;
                $schools = config('chcschool.schools');
                $att['school'] = $schools[$data->school_no];
                $att['title'] = $data->title_name;
                $att['role'] = $data->role;
                $att['title_kind'] = $data->title_kind;
                $att['group'] = $data->group;

                $user->update($att);
            }

            Auth::login($user);

            return redirect()->route('index');
        }


    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('index');
    }

    public function cloud_school()
    {

    }
}
