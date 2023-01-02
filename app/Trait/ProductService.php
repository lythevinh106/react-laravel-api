<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Product;
use App\Models\Product_images;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Foreach_;

trait ProductService
{
    use UploadService;


    public function create_product($request)
    {

        DB::beginTransaction();
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:100|required",
                    "origin_price" => "min:3|integer|gt:sale_price",
                    "sale_price" => "min:3|integer",
                    "title" => "min:3|max:300|required",
                    "image" => "max:1000000|mimes:jpg,jpeg,png,jpe,gif",
                    "description" => "required",
                    "category_id" => "integer|required",


                ],

            );

            if ($validator->fails()) {

                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {

                $request->except("product_images");
                $request->except("image");

                $path =  $this->upload_image($request->file("image"));
                $product = Product::create($request->all());
                Product::find($product->id)->update(["image" => $path]);

                ////insert product_images
                if ($request->has("product_images")) {

                    foreach ($request->file("product_images") as $image) {

                        $path = $this->upload_image($image);

                        Product_images::create(
                            [
                                "product_id" => $product->id,
                                "name" => $path
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "create product Success ",
                "data" => $product

            ], 200);
        } catch (\Exception $err) {

            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $err->getMessage(),

            ], 400);
        }
    }










    public function update_product($request, $product_id)
    {

        DB::beginTransaction();


        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:100|required",
                    "origin_price" => "min:3|integer|gt:sale_price",
                    "sale_price" => "min:3|integer",
                    "title" => "min:3|max:300|required",
                    "image" => "required",
                    "description" => "required",
                    "category_id" => "integer|required",


                ],

            );


            if ($validator->fails()) {


                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {

                $product_exist = Product::where("id", $product_id)->count();;


                if ($product_exist <= 0) {

                    return response()->json([
                        "status" => 400,
                        "message" => "Sản phẩm Không Có Trong Hệ Thống vui lòng kiểm tra lại id",


                    ], 400);
                } else {

                    $query = Product::find($product_id);
                    $query->fill($request->input());

                    if ($request->has("image")) {
                        $this->delete_s3($query->image);
                        $path_image =  $this->upload_image($request->file("image"));
                        $query->image = $path_image;
                    }
                    $query->save();
                    //// delete old product image
                    $product_images_delete = Product_images::where("product_id", $product_id)->get();
                    if ($product_images_delete->first() == true) {
                        foreach ($product_images_delete as $image) {
                            // dd($image->name);
                            $this->delete_s3($image->name);
                        }
                    }
                    //// update product image
                    Product::find($product_id)->product_images()->delete();
                    if ($request->has("product_images")) {
                        // dd($request->file("product_images"));
                        foreach ($request->file("product_images") as $image) {

                            $path = $this->upload_image($image);
                            Product_images::create([
                                "product_id" => $product_id,
                                "name" => $path

                            ]);
                        }
                    }

                    // die();
                }

                DB::commit();

                return response()->json([
                    "code" => "200",
                    "message" => "cập nhật sản phẩm thành công ",
                    "data" => Product::find($product_id)
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


    public function delete_product($product_id)
    {

        DB::beginTransaction();
        try {

            $checkExits = Product::where("id", $product_id)->count();

            if ($checkExits <= 0) {

                return response()->json([
                    "code" => "400",
                    "message" => "Sản Phẩm không được tìm thấy trên hệ thống"
                ], 400);
            } else {

                $check_exist_product_image = Product_images::where("product_id", $product_id)->count();


                if ($check_exist_product_image > 0) {
                    foreach (Product_images::where("product_id", $product_id)->get() as $image) {

                        $this->delete_s3($image->name);
                    }
                }



                $product_query = Product::find($product_id);
                $this->delete_s3($product_query->image);
                $product_query->delete();


                DB::commit();

                return response()->json([
                    "code" => "200",
                    "message" => "Sản Phẩm Được Xóa Thành Công",

                ], 200);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }
}
