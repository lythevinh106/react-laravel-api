<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Product_images;
use App\Trait\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ProductService;


    public function list(Request $request)
    {

        $response = $this->show_products($request);

        return response()->json($response);
    }
    public function index()
    {
        $const = Product::find(1);

        return response()->json($const);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->input("name"));

        $response_product = $this->create_product($request);



        return response()->json($response_product);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {



        $count = Product::where("id", $id)->count();
        if ($count < 1) {

            return response()->json(
                [
                    "code" => 400,
                    "message" => "not found product",

                ],
                400
            );
        } else {


            $data_product = Product::find($id);
            $data_product_images = Product::find($id)->product_images()->get();
            return response()->json(
                [
                    "code" => 200,
                    "message" => "Get Product Success",
                    "data" => [
                        "product" => $data_product,
                        "product_images" => $data_product_images
                    ]
                ],
                200
            );
        }
    }


    public function show_product_images($id)
    {

        $product_images = Product::find($id)->product_images()->get();
        // dd($product_images);

        return response()->json([
            "code" => 200,
            "data" =>  $product_images
        ], 200);
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
        // dd($request->all());
        $response_product = $this->update_product($request, $id);

        return response()->json($response_product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response_product = $this->delete_product($id);



        return response()->json($response_product);
    }


    public function show_other_product(Request $request, $product_id)
    {



        $exist_category = Product::find($product_id)->category()->first("id")->id;


        $limit = $request->limit ?? 7;
        if ($exist_category  >= 1) {

            $data = Product::where("category_id", $exist_category)->inRandomOrder()->limit($limit)->get();

            return response()->json([
                "code" => 200,
                "message" => "Get Product success",
                "data" => $data
            ], 200);
        } else {


            return response()->json([
                "code" => 400,
                "message" => "product not found",

            ], 400);
        }
    }
}
