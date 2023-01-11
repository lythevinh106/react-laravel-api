<?php

namespace App\Trait;

use App\Jobs\SendMailWhenOrderSuccess;
use App\Mail\SendMailOrderSuccess;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Order_items;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


trait CartService
{


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














                DB::commit();


                SendMailWhenOrderSuccess::dispatch($customer->email, $data);

                // Mail::to($customer->email)->send(new SendMailOrderSuccess($data));

                return response()->json([
                    "status" => 200,
                    "message" => "Thêm Giỏ Hàng Thành Công ",


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
}
