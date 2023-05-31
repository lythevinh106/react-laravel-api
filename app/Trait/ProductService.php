<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Product_images;
use App\Models\Rate;
use App\Models\User;
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

        // dd($query_product->with('rates')->paginate($limit)->withQueryString());

        // $query_product->inRandomOrder();

        return $query_product->with(['rates' => function ($query) {
            $query->select('product_id', DB::raw('AVG(rating) as average_rating'))
                ->groupBy('product_id');
        }])->paginate($limit)->withQueryString();
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


    public function update_rating($request, $product_id)
    {


        // $checkExitsRating = Rate::where("product_id", $product_id)->where("user_id", $request->input("userID"))->count();


        DB::beginTransaction();
        try {

            $user = Rate::updateOrCreate(
                [
                    'product_id' => $product_id,
                    'user_id' => $request->input("userID"),
                ],
                [
                    'rating' => $request->input("rating"),
                    'user_id' => $request->input("userID"),
                    'product_id' =>  $product_id
                ]
            );





            DB::commit();


            return response()->json([
                "code" => "200",
                "message" => "Đánh Giá Sản Phẩm Thành Công",

            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }


    public function check_rating($request, $product_id)
    {
        // dd("sad");

        DB::beginTransaction();
        try {
            $checkExitsRating = Rate::where("product_id", $product_id)->where("user_id", $request->input("userID"))->count();

            if ($checkExitsRating >= 1) {
                $result =  Rate::where("product_id", $product_id)->where("user_id", $request->input("userID"))->value("rating");
            } else {
                $result = 0;
            }

            DB::commit();


            return response()->json([
                "code" => "200",
                "result" => $result,
                "message" => "Check rating thành công",

            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }


    public function add_comment($request, $product_id)
    {

        DB::beginTransaction();
        try {

            $data = [
                'parent_comment_id' => $request->input("parentCommentID"),
                'user_id' => $request->input("userID"),
                'product_id' => $product_id,
                'comment_text' => $request->input("commentText"),


            ];


            // dd($data);

            $comment = Comment::create($data);
            // $user = User::find($request->userID);
            $comment->user_name = $comment->user->name;

            DB::commit();




            return response()->json([
                "code" => "200",
                "data" => $comment,
                "message" => "Thêm Comment thành công",

            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }



    public function show_comment($request, $product_id)
    {

        DB::beginTransaction();
        try {




            // dd($data);

            $comment = Comment::where('product_id', $product_id)->with('user') // Tải thông tin từ bảng users
                ->get();

            

            DB::commit();




            return response()->json([
                "code" => "200",
                "data" => $comment,
                "message" => "show Comment thành công",

            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $e->getMessage(),

            ], 400);
        }
    }
}