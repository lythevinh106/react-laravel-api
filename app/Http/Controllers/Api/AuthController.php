<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Trait\AuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    use AuthService;


    // public function __construct()
    // {
    //     $this->middleware("jwtauth")->except("register", "login");
    // }


    public function __construct()
    {
        $this->middleware('jwtAuth', ['except' => ['login', 'register']]);
    }






    public function index()
    {
        //
    }

    public function register(Request $request)
    {
        $response = $this->register_account($request);

        return response()->json($response);
    }
    public function verify(Request $request, $id, $token)
    {
        $response = $this->verify_account($request, $id, $token);

        return $response;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }





    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        // $token = Auth::attempt($credentials);


        $token = JWTAuth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 400,
                'message' => 'info not valid',

            ], 400);
        }

        $refresh_token = Str::random(20);
        $user = Auth::user();
        $user->refresh_token = $refresh_token;
        $user->save();
        // dd($user->refresh_token);
        return response()->json([
            'status' => 201,
            'user' => $user,

            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                "refresh_token" =>  $refresh_token

            ]
        ], 201);
    }

    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     $token = Auth::login($user);
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'User created successfully',
    //         'user' => $user,
    //         'authorisation' => [
    //             'token' => $token,
    //             'type' => 'bearer',
    //         ]
    //     ]);
    // }

    public function logout()
    {

        Auth::user()->refresh_token = " ";
        Auth::user()->save();

        // dd(Auth::user()->refresh_token);
        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
        ]);
    }

    public function refresh($refresh_token)
    {
        $payload = auth()->payload();

        if (Auth::user()->refresh_token == $refresh_token) {
            return response()->json([
                'status' => 'success',
                'user' => Auth::user(),
                'authorisation' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'user' => Auth::user(),
                'authorisation' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                ]
            ]);
        }
    }

    public function payload()
    {
        // dd(time());

        $payload = auth()->payload();
        // if ($payload[""])


        // then you can access the claims directly e.g.
        // $payload->get('sub'); // = 123
        // $payload['jti']; // = 'asfe4fq434asdf'
        // $payload('exp'); // = 123456;
        // $payload->toArray();
        dd($payload->toArray());
    }
}
