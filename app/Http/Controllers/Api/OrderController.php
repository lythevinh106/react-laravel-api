<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Trait\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    use OrderService;




    public function checkToken($token)

    {
        $response = $this->check_token($token);



        return response()->json($response);
    }

    public function showAll(Request $request)

    {
        $response = $this->list_order($request);



        return response()->json($response);
    }


    public function ShowDetail($order_id)

    {
        $response = Order::with(["user", "customer"])->find($order_id);
        // if ($response->user != null) {
        //     $response->with("user");
        // } else {
        //     $response->with("customer");
        // }




        return response()->json([
            "status" => 201,
            "data" => $response
        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function removeOrder($order_id)
    {


        $response = Order::find($order_id)->delete();


        return response()->json([
            "status" => 201,
            "message" => "Xóa Thành Công Đơn Hàng",
            "data" => ["order_id" => $order_id]
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $response = $this->updateStatusOrder($request);




        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
