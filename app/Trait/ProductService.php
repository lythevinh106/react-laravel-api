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
use Illuminate\Database\Eloquent\Builder;

trait ProductService
{
    use UploadService;






    public function show_products($request)
    {
        // dd($request->all());

        $limit = $request->limit ?? 5;
        $sort = $request->sort ?? "asc";






        // $query =  Product::orderBy("id", $sort);

        if ($request->has("random")) {
            $query_product =  Product::inRandomOrder();
        } else {
            $query_product =  Product::orderBy("id", $sort);
        }

        if ($request->has("search")) {


            $query_product->where("name", "LIKE", "%$request->search%");
        }

        if ($request->has("slug_cat")) {


            $query_product->whereHas('category', function (Builder $query) use ($request) {

                $query->where('slug', $request->input("slug_cat"));
            });
        }



        if ($request->has("from")) {

            $query_product->whereBetween('sale_price', [$request->input("from"), $request->input("to")]);
        }




        if ($request->has("cat")) {
            $query_product->where('category_id', $request->input("cat"));
        }


        // $query_product->inRandomOrder();

        return $query_product->paginate($limit)->withQueryString();
    }


    public function create_product($request)
    {

        DB::beginTransaction();
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:1000|required",
                    "origin_price" => "min:3|integer|gt:sale_price",
                    "sale_price" => "min:3|integer",
                    "title" => "min:3|max:3000|required",
                    "image" => "max:10000000|mimes:jpg,jpeg,png,jpe,gif",
                    "description" => "required",
                    "category_id" => "integer|required",


                ],

            );

            if ($validator->fails()) {

                return response()->json([
                    "status" => 400,
                    "message" => "d??? li???u kh??ng ????ng ??inh d???ng",
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

                    "description" => "required",
                    "category_id" => "integer|required",


                ],

            );


            if ($validator->fails()) {


                return response()->json([
                    "status" => 400,
                    "message" => "d??? li???u kh??ng ????ng ??inh d???ng",
                    "error" => $validator->errors()

                ], 400);
            } else {
                $category_exist = Category::where("id", $request->input("category_id"))->first();
                // dd($category_exist->is_null());
                if ($category_exist === null) {


                    return response()->json([
                        "status" => 400,
                        "message" => "category is not exist",


                    ], 400);
                } else {


                    $product_exist = Product::where("id", $product_id)->count();




                    if ($product_exist <= 0) {

                        return response()->json([
                            "status" => 400,
                            "message" => "S???n ph???m Kh??ng C?? Trong H??? Th???ng vui l??ng ki???m tra l???i id",


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
                        if ($request->has("image")) {


                            $product_images_delete = Product_images::where("product_id", $product_id)->get();
                            if ($product_images_delete->first() == true) {
                                foreach ($product_images_delete as $image) {
                                    // dd($image->name);
                                    $this->delete_s3($image->name);
                                }
                            }
                        }
                        //// update product image

                        if ($request->has("product_images")) {
                            // dd($request->file("product_images"));
                            Product::find($product_id)->product_images()->delete();
                            foreach ($request->file("product_images") as $image) {

                                $path = $this->upload_image($image);
                                Product_images::create([
                                    "product_id" => $product_id,
                                    "name" => $path

                                ]);
                            }
                        }
                    }

                    // die();
                }

                DB::commit();

                return response()->json([

                    "status" => 200,
                    "message" => "c???p nh???t s???n ph???m th??nh c??ng ",
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
                    "message" => "S???n Ph???m kh??ng ???????c t??m th???y tr??n h??? th???ng"
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
                    "message" => "S???n Ph???m ???????c X??a Th??nh C??ng",

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
