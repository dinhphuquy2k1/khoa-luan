<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DB::table('users')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function sendEmailVerify(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attribute = $request->validate(
            [
                'email' => 'required|unique:users,email',
                'username' => 'required',
                'usercode' => 'required|unique:users,usercode',
                'level' => 'required',
                'note' => '',
            ],
            [
                'email.required' => 'Email không được để trống',
                'email.unique' => 'Email dã tồn tại',
                'username.required' => 'Họ và tên không được để trống',
                'usercode.unique' => 'Mã tài khoản đã tồn tại',
                'level.required' => 'Loại tài khoản không được để trống'
            ]
        );

        $attribute['Password'] = bcrypt('123456');
        User::insert($attribute);
    }


    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Tài khoản hoặc mật khẩu không đúng!']);
        }
        $attributes = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Tài khoản hoặc mật khẩu không đúng!'], 401);
        }
        if (\Auth::attempt(['Email' => $request->email, 'password' => $request->password])) {
            $accessToken = auth()->user()->createToken('authToken')->accessToken;
            return response()->json(['success' => 'successAdmin', 'user' => auth()->user(), 'access_token' => $accessToken], 200);
        }

        return response()->json(['error' => 'Ooops! something went wrong!']);
    }

    /**
     * Kiểm tra password user
     */
    public function checkpassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!\Hash::check($request->passwordCheck, $user->password)) {
            return response()->json(['error' => 'Mật khẩu không đúng!'], 401);
        }
        return response()->json(['success' => 'welcome'], 200);
    }

    /**
     * Kiểm tra email đã có chưa
     */
    public function checkEmailExits(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['error' => 'Email đã tồn tại!'], 401);
        }
        return response()->json(['success' => 'welcome'], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $attribute = $request->validate(
            [
                'email' => "required|unique:users,email,{$request->id}",
                'username' => 'required',
                'usercode' => "required|unique:users,usercode,{$request->usercode},usercode",
                'level' => 'required',
                'note' => '',
            ],
            [
                'email.required' => 'Email không được để trống',
                'email.unique' => 'Email dã tồn tại',
                'username.required' => 'Họ và tên không được để trống',
                'usercode.unique' => 'Mã tài khoản đã tồn tại',
                'level.required' => 'Loại tài khoản không được để trống'
            ]
        );

        User::find($request->id)->update($attribute);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        User::find($request->id)->delete();
    }
}
