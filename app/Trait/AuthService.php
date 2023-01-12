<?php

namespace App\Trait;

use App\Jobs\SendMailWhenOrderSuccess;
use App\Jobs\SendMailWhenRegiterAccount;
use App\Mail\SendMailOrderSuccess;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\Product;
use App\Models\User;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PSpell\Config;
use Carbon\Carbon;

trait AuthService
{
    use CartService;

    public function register_account($request)
    {



        DB::beginTransaction();

        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "email" => "min:3|max:1000|required",
                    "address" => "min:4|max:1000|required",
                    "phone" => "min:10|required",
                    "password" => "min:3|max:100",
                    "name" => "min:3|max:1000|required"


                ],

            );


            if ($validator->fails()) {



                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {


                $exist_email = User::where("email", $request->email)->count();

                // dd($exist_email);
                if ($exist_email >= 1) {

                    return response()->json([
                        "status" => 203,
                        "message" => "Email Đã Tồn Tại Trong Hệ Thống ",


                    ], 203);
                } else {




                    ///insert user

                    $user = User::create([
                        "name" => $request->name,
                        "email" => $request->email,
                        "address" => $request->address,
                        "phone" => $request->phone,
                        "password" => Hash::make($request->password),
                        "remember_token" => $this->create_token()
                    ]);




                    $data = [
                        "link" => url("/") . "/api/auth/verify/" . $user->id . "/" . $user->remember_token,



                    ];


                    // dd($data["link"]);



                    // SendMailWhenRegiterAccount::dispatch($user->email, $data);
                    SendMailWhenRegiterAccount::dispatch($request->email, $data);

                    DB::commit();




                    // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));

                    return response()->json([
                        "status" => 200,
                        "message" => "Tạo Tài Khoản  Thành Công ",


                    ]);
                }
            }
        } catch (\Exception $e) {


            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }




    public function verify_account($request, $id, $token)
    {
        // dd($id, $token);


        DB::beginTransaction();

        try {





            $exist_email = User::where("id", $id)->count();

            // dd($exist_email);
            if ($exist_email < 1) {

                dd("email không tồn tại trong hệ thống");
            } else {




                ///insert user

                $user = User::find($id);

                if ($user->remember_token == $token) {

                    $user->update(["email_verified_at" => Carbon::now()]);
                    DB::commit();
                    return view("mails.alert_verify");
                }







                // dd($data["link"]);



                // SendMailWhenRegiterAccount::dispatch($user->email, $data);







                // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));

                // return response()->json([
                //     "status" => 200,
                //     "message" => "Tạo Tài Khoản  Thành Công ",


                // ]);
            }
        } catch (\Exception $e) {


            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }
}
