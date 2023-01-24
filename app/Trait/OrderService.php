<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Product_images;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\TryCatch;

trait OrderService
{

    public function list_order($request)
    {

        // dd($request->all());

        $limit = $request->limit ?? 5;
        $sort = $request->sort ?? "asc";

        $query_order =  Order::with(["user", "customer", "products", "order_items"])->orderBy("id", $sort);


        if ($request->has("search")) {




            $query_order->where("order_token", "like", "%$request->search%");
        }

        //  if ($request->has("slug_cat")) {


        //     $query_order->whereHas('category', function (Builder $query) use ($request) {

        //         $query->where('slug', $request->input("slug_cat"));
        //     });
        // }



        if ($request->has("status")) {
            $query_order->where('status', $request->input("status"));
        }











        return $query_order->paginate($limit)->withQueryString();
    }




    public function check_token($token)
    {

        // dd($request->all());


        $exist_token = Order::where("order_token", $token)->count();
        // dd(Order::with(["user", "customer", "products", "order_items"])
        //     ->where("order_token", $token)->get());

        // dd($exist_token);
        if ($exist_token <= 0) {
            return response()->json([
                "status" => 400,
                "message" => "Không tìm thấy mã đơn hàng trên hê thống"

            ]);
        } else {
            return response()->json([
                "status" => 201,
                "message" => "tìm thấy đơn hàng thành công",
                "data" => Order::with(["user", "customer", "products", "order_items"])
                    ->where("order_token", $token)->get()

            ]);
        }



        // $query_order =  Order::with(["user", "customer", "products", "order_items"])->orderBy("id", $sort);













        // return $query_order->paginate($limit)->withQueryString();
    }






    public function updateStatusOrder($request)
    {


        DB::beginTransaction();
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "status" => "required",
                    "order_id" => "required",

                ],

            );

            $category_exist = Category::where("slug", $request->input("slug"))->count();
            // dd($category_exist);

            if ($validator->fails()) {


                // dd($validator->errors());


                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            }

            Order::find($request->order_id)->update(["status" => $request->status]);





            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "Update Trạng Thái Đơn Hàng Thành Công",
                "data" => ["order_id" => $request->order_id, "status" => $request->status]


            ], 200);
        } catch (Exception $err) {

            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $err->getMessage(),

            ], 400);
        }
    }
}
