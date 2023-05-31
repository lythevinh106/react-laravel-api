<?php

namespace App\Trait;

use App\Events\AlertOrderProduct;
use App\Jobs\SendMailWhenOrderSuccess;
use App\Mail\SendMailOrderSuccess;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Notification as ModelsNotification;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\Product;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportUser;
use App\Exports\ExportUser;

trait CartService
{

    public function add_cart_for_auth($request)
    {

        if (Auth::user()) {
            // dd("da dang nhap");
            DB::beginTransaction();

            try {

                $validator = Validator::make(
                    $request->all(),
                    [
                        "user_id" => "required|integer",
                        "product_id" => "required|integer",
                        "quantity" => "required|integer",
                    ],

                );

                if ($validator->fails()) {
                    return response()->json([
                        "status" => 400,
                        "message" => "dữ liệu không đúng đinh dạng",
                        "error" => $validator->errors()

                    ], 400);
                } else {

                    ///insert cart
                    $cart =  Cart::where("user_id", $request->user_id)->where("product_id", $request->product_id);
                    $exist = $cart->count();
                    if ($exist <= 0) {

                        Cart::create(

                            [
                                'user_id' =>   $request->user_id,
                                'product_id' =>   $request->product_id,
                                'quantity' =>   $request->input("quantity"),
                            ]

                        );
                    } else {
                        $oldQuantity = $cart->value("quantity");
                        // dd($oldQuantity);

                        $cart->update(
                            [
                                'user_id' =>   $request->user_id,
                                'product_id' =>    $request->product_id,
                                'quantity' =>    $oldQuantity + $request->quantity
                            ],
                        );
                    }

                    DB::commit();

                    return response()->json([
                        "status" => 200,
                        "message" => "Thêm Vào Giỏ Hàng Thành Công ",


                    ], 200);
                }
            } catch (\Exception $e) {


                DB::rollBack();

                return response()->json([
                    "code" => "400",
                    "message" => $e->getMessage(),

                ], 400);
            }
        } else {

            return response()->json([
                "code" => "400",
                "message" => "auth not valid",

            ], 400);
        }
    }

    public function update_cart_for_auth($request)
    {
        // dd($request->all(), $useId);
        if (Auth::user()) {
            // dd("da dang nhap");
            DB::beginTransaction();

            try {

                $validator = Validator::make(
                    $request->all(),
                    [
                        "user_id" => "required|integer",
                        "product_id" => "required|integer",
                        "quantity" => "required|integer",
                    ],

                );

                if ($validator->fails()) {
                    return response()->json([
                        "status" => 400,
                        "message" => "dữ liệu không đúng đinh dạng",
                        "error" => $validator->errors()

                    ], 400);
                } else {

                    ///insert cart
                    $cart =  Cart::where("user_id", $request->user_id)->where("product_id", $request->product_id);
                    $exist = $cart->count();
                    if ($exist > 0) {

                        $cart->update(

                            [
                                'user_id' =>   $request->user_id,
                                'product_id' =>   $request->product_id,
                                'quantity' =>   $request->input("quantity"),
                            ]

                        );
                    } else {


                        return response()->json([
                            "status" => 400,
                            "message" => "không tìm thấy thông tin giỏ hàng ",


                        ], 400);
                    }

                    DB::commit();

                    return response()->json([
                        "status" => 200,
                        "message" => "Cập Nhật Giỏ Hàng Thành Công ",


                    ], 200);
                }
            } catch (\Exception $e) {


                DB::rollBack();

                return response()->json([
                    "code" => "400",
                    "message" => $e->getMessage(),

                ], 400);
            }
        } else {

            return response()->json([
                "code" => "400",
                "message" => "auth not valid",

            ], 400);
        }
    }





    public function delete_cart_for_auth($request)
    {



        // dd($request->all(), $useId);
        if (Auth::user()) {
            // dd("da dang nhap");
            DB::beginTransaction();

            try {

                $validator = Validator::make(
                    $request->all(),
                    [
                        "user_id" => "required|integer",
                        "product_id" => "required|integer",

                    ],

                );

                if ($validator->fails()) {
                    return response()->json([
                        "status" => 400,
                        "message" => "dữ liệu không đúng đinh dạng",
                        "error" => $validator->errors()

                    ], 400);
                } else {

                    ///insert cart
                    $cart =  Cart::where("user_id", $request->user_id)->where("product_id", $request->product_id);
                    $exist = $cart->count();
                    if ($exist > 0) {

                        $cart->delete();
                    } else {


                        return response()->json([
                            "status" => 400,
                            "message" => "không tìm thấy thông tin giỏ hàng ",


                        ], 400);
                    }

                    DB::commit();

                    return response()->json([
                        "status" => 200,
                        "message" => "Xóa Sản Phẩm Trong  Giỏ Hàng Thành Công ",


                    ], 200);
                }
            } catch (\Exception $e) {


                DB::rollBack();

                return response()->json([
                    "code" => "400",
                    "message" => $e->getMessage(),

                ], 400);
            }
        } else {

            return response()->json([
                "code" => "401",
                "message" => "auth not valid",

            ], 401);
        }
    }



    public function remove_all_cart_for_auth($request)
    {



        // dd($request->all());
        if (Auth::user()) {
            // dd("da dang nhap");
            DB::beginTransaction();

            try {

                $validator = Validator::make(
                    $request->all(),
                    [
                        "user_id" => "required|integer",


                    ],

                );

                if ($validator->fails()) {
                    return response()->json([
                        "status" => 400,
                        "message" => "dữ liệu không đúng đinh dạng",
                        "error" => $validator->errors()

                    ], 400);
                } else {

                    ///insert cart
                    $cart =  Cart::where("user_id", $request->user_id);
                    $exist = $cart->count();
                    if ($exist > 0) {

                        $cart->delete();
                    } else {


                        return response()->json([
                            "status" => 400,
                            "message" => "không tìm thấy thông tin giỏ hàng ",


                        ], 400);
                    }

                    DB::commit();

                    return response()->json([
                        "status" => 200,
                        "message" => "Xóa Sản Phẩm Trong  Giỏ Hàng Thành Công ",


                    ], 200);
                }
            } catch (\Exception $e) {


                DB::rollBack();

                return response()->json([
                    "code" => "400",
                    "message" => $e->getMessage(),

                ], 400);
            }
        } else {

            return response()->json([
                "code" => "401",
                "message" => "auth not valid",

            ], 401);
        }
    }



    public function show_cart_for_auth($useId)
    {



        if (Auth::user()) {
            // dd("da dang nhap");
            DB::beginTransaction();

            try {
                ///insert cart
                $cart =  Cart::where("user_id", $useId);
                $exist = $cart->count();
                if ($exist > 0) {


                    $cart_info = $cart->get();

                    // dd($cart_info);
                    $data = [];

                    foreach ($cart_info  as $key => $item) {
                        $info_product = Product::find($item->product_id);

                        $data[$key] = [
                            "id" => $item->product_id,
                            "product" => [
                                "oldPrice" => $info_product->value("origin_price"),
                                "newPrice" => $info_product->value("sale_price"),
                                "image" => $info_product->image,
                                "title" => $info_product->name,
                            ],
                            "quantity" => $item->quantity

                        ];
                    }

                    // dd($data);

                    return response()->json([
                        "status" => 201,
                        "message" => "Show Giỏ Hàng thành công",
                        "data" => $data


                    ], 201);
                } else {
                    return response()->json([
                        "status" => 200,
                        "message" => "Giỏ Hàng Trống",



                    ], 201);
                }










                DB::commit();




                // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));

                // return response()->json([
                //     "status" => 200,
                //     "message" => "Show Giỏ Hàng Thành Công ",


                // ]);
            } catch (\Exception $e) {


                DB::rollBack();

                return response()->json([
                    "code" => "400",
                    "message" => $e->getMessage(),

                ], 400);
            }
        } else {

            return response()->json([
                "code" => "401",
                "message" => "auth not valid",

            ], 400);
        }
    }





    public function add_cart($request)
    {

        // dd($request->all());

        DB::beginTransaction();

        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:1000|required",
                    "address" => "min:4|max:1000|required",
                    "phone" => "min:10|required",
                    "email" => "required",
                    "note" => "required",

                    "cart_info" => "required|array",


                ],

            );


            if ($validator->fails()) {



                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {



                ///insert Customer table
                $request->except("cart_info");
                $customer = Customer::create($request->all());
                ///insert order
                $order_create = Order::create([
                    "customer_id" =>    $customer->id,
                    "order_date" => Carbon::now(),
                    "order_token" => $this->create_token(),

                    "status" => 0
                ]);


                ///insert order_item
                $info_products =  $request->input("cart_info");
                // dd($request->input("cart_info"));

                // dd($info_products[0]["id"]);
                foreach ($info_products as $info_product) {
                    // dd($info_product["id"]);
                    Product::find($info_product["id"])->orders()
                        ->attach($order_create->id, ["quantity" => $info_product["quantity"]]);
                }


                $data_order =   Order_items::where("order_id", $order_create->id)->get();
                $total_price = 0;
                foreach ($data_order as $order) {
                    $total_price += $order->quantity * $order->product->sale_price;
                }


                $data = [
                    "time_order" => Carbon::now()->format("d m Y \\và\\o \l\ú\c H \G\i\ờ i \P\h\ú\\t s \G\i\â\y"),
                    "name" => $customer->name,
                    "email" => $customer->email,
                    "order_token" => $order_create->order_token,
                    "address" => $customer->address,
                    "note" => $customer->note,
                    "phone" => $customer->phone,
                    "order_items" => Order_items::where("order_id", $order_create->id)->get(),
                    "total" => $total_price

                ];

                $data_alert = [
                    "order_token" =>  $order_create->order_token,
                    "phone" =>  $customer->phone,
                    "order_item" => Order_items::where("order_id", $order_create->id)->get(),
                    "is_read" => 0,

                ];

                $newDataAlert = Notification::create($data_alert);

                $data_alert["id"] = $newDataAlert->id;

                DB::commit();


                SendMailWhenOrderSuccess::dispatch($customer->email, $data);



                event(new AlertOrderProduct($data_alert));
                // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));

                return response()->json([
                    "status" => 200,
                    "message" => "Đặt Hàng Thành Công ",


                ]);
            }
        } catch (\Exception $e) {


            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }


    public function payment_for_auth($request)
    {

        // dd($request->userId);

        DB::beginTransaction();

        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "user_id" => "required|integer",



                ],

            );


            if ($validator->fails()) {



                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {




                $order_create = Order::create([
                    "user_id" =>    $request->user_id,
                    "order_date" => Carbon::now(),
                    "order_token" => $this->create_token(),
                    "status" => 0
                ]);


                ///insert order_item
                $info_products = Cart::where("user_id", $request->user_id)->get();;

                // dd($info_products);
                // dd($request->input("cart_info"));

                // dd($info_products[0]["id"]);
                foreach ($info_products as $info_product) {
                    // dd($info_product["id"]);
                    Product::find($info_product["product_id"])->orders()
                        ->attach($order_create->id, ["quantity" => $info_product["quantity"]]);
                }



                ////delete_cart_when_order

                Cart::where("user_id", $request->user_id)->delete();


                ///set total for mail

                $data_order =   Order_items::where("order_id", $order_create->id)->get();
                $total_price = 0;

                foreach ($data_order as $order) {
                    $total_price += $order->quantity * $order->product->sale_price;
                }


                $user_info = User::find($request->user_id);


                $data = [
                    "time_order" => Carbon::now()->format("d m Y \\và\\o \l\ú\c H \G\i\ờ i \P\h\ú\\t s \G\i\â\y"),
                    "name" =>  $user_info->name,
                    "email" => $user_info->email,
                    "order_token" => $order_create->order_token,
                    "address" => $user_info->address,
                    "note" => " ",
                    "phone" => $user_info->phone,
                    "order_items" => Order_items::where("order_id", $order_create->id)->get(),
                    "total" => $total_price

                ];


                $data_alert = [
                    "order_token" =>  $order_create->order_token,
                    "phone" =>  $user_info->phone,
                    "order_item" => Order_items::where("order_id", $order_create->id)->get(),
                    "is_read" => 0,

                ];

                $newDataAlert = Notification::create($data_alert);

                $data_alert["id"] = $newDataAlert->id;


                DB::commit();


                SendMailWhenOrderSuccess::dispatch($user_info->email, $data);
                // return ["data" => $data, "data_order" => $data_order];

                event(new AlertOrderProduct($data_alert));
                // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));
                // Excel::download(new ExportUser, 'users.xlsx');
                return response()->json([
                    "status" => 200,
                    "message" => "Thanh Toán Giỏ Hàng Thành Công ",


                ]);
            }
        } catch (\Exception $e) {


            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }

    function create_token()
    {
        return time() . Str::random(10);
    }


    function all_nofi()
    {



        try {


            $data = Notification::take(6)->orderBy('is_read', 'asc')->orderBy('id', 'desc')->get();


            DB::commit();
            return response()->json([
                "status" => 201,
                "data" => $data,
                "message" => "Show tất cả thông báo",


            ], 200);
        } catch (\Exception $e) {


            DB::rollBack();

            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }

    function update_all_nofi()
    {



        try {


            $records = Notification::all();

            foreach ($records as $record) {
                $record->update([
                    'is_read' => true,
                ]);
            }


            DB::commit();
            return response()->json([
                "status" => 201,

                "message" => "update nofi thành công",


            ], 200);
        } catch (\Exception $e) {


            DB::rollBack();

            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }
}
