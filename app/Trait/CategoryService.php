<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


trait CategoryService
{



    public function create_category($request)
    {


        try {

            $validator = Validator::make(
                $request->all(),
                [
                    "name" => "min:4|max:100|required",
                    "slug" => "min:3|max:100|required",

                ],

            );

            $category_exist = Category::where("name", $request->input("name"))->count();
            // dd($category_exist);

            if ($validator->fails()) {


                // dd($validator->errors());


                return response()->json([
                    "status" => 400,
                    "message" => "dữ liệu không đúng đinh dạng",
                    "error" => $validator->errors()

                ], 400);
            } else {

                if ($category_exist >= 1) {
                    return response()->json([
                        "status" => 400,
                        "message" => "Tên Danh Mục Bị Trùng Bạn Hãy Tạo 1 Danh Mục Khác",


                    ], 400);
                }

                $category = Category::create($request->all());
                return response()->json([
                    "status" => 200,
                    "message" => "create category Success ",
                    "data" => $category

                ], 200);
            }
        } catch (\Throwable $err) {

            return $err->getMessage();
            return false;
        }
    }










    public function update_category($request, $category)
    {
        // try {

        //     $query = Category::find($category);
        //     $query->fill($request->input());
        //     $query->save();





        //     return redirect()->back()->with("success", "Chỉnh sửa thông tin  thành công");
        // } catch (\Exception $err) {
        //     return redirect()->back()->with("error", $err->getMessage());
        // }
    }


    public function delete_category($category_id)
    {

        $checkExits = Category::where("id", $category_id)->count();

        if ($checkExits <= 0) {
            return response()->json([
                "code" => "400",
                "message" => "Danh Mục Không hợp lệ hoặc không tìm thấy trên hệ thống nữa"
            ], 400);
        } else {
            Category::where("id", $category_id)->delete();
            return response()->json([
                "code" => "200",
                "message" => "Danh Mục Được Xóa Thành Công"
            ], 200);
        }




        // $query->delete();


    }
}
