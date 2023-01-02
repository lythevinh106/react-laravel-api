<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


trait CategoryService
{



    public function create_category($request)
    {

        DB::beginTransaction();
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:100|required",
                    "slug" => "min:3|max:100|required",

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


            if ($category_exist >= 1) {
                return response()->json([
                    "status" => 400,
                    "message" => "Tên slug Danh Mục Bị Trùng Bạn Hãy Tạo 1 Danh Mục Khác",


                ], 400);
            }

            $category = Category::create($request->all());



            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "create category Success ",
                "data" => $category

            ], 200);
        } catch (Exception $err) {

            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $err->getMessage(),

            ], 400);
        }
    }










    public function update_category($request, $category_id)
    {

        DB::beginTransaction();
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:100|required",
                    "slug" => "min:3|max:100|required",

                ],

            );

            $category_exist = Category::where("id", $category_id)->count();;
            if ($validator->fails()) {
                // dd($validator->errors());
                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {
                if ($category_exist <= 0) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Tên Danh Mục Không Có Trong Hệ Thống",


                    ], 400);
                }

                $query = Category::find($category_id);
                $query->fill($request->input());
                $query->save();
            }
            DB::commit();
            return response()->json([
                "code" => "200",
                "message" => "cập nhật danh mục thành công",
                "data" => $query
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $err->getMessage(),

            ], 400);
        }
    }


    public function delete_category($category_id)
    {



        DB::beginTransaction();
        try {


            $checkExits = Category::find($category_id)->count();


            if ($checkExits <= 0) {
                return response()->json([
                    "code" => "400",
                    "message" => "Danh Mục Không hợp lệ hoặc không tìm thấy trên hệ thống nữa"
                ], 400);
            }

            Category::find($category_id)->delete();


            DB::commit();
            return response()->json([
                "code" => "200",
                "message" => "Danh Mục Được Xóa Thành Công"
            ], 200);
        } catch (\Exception $err) {
            // DB::rollBack();
            return response()->json([
                "code" => "400",
                "message" => $err->getMessage(),

            ], 400);
        }

        // $query->delete();


    }
}
