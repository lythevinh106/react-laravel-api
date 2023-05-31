<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Trait\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use CartService;

    public function __construct()
    {
        $this->middleware(['jwtAuth'], ['only' => ['add_cart_auth', 'show_cart_auth', 'update_cart_auth', 'payment_for_auth']]);
    }
    public function index()
    {
    }


    public function payment_auth(Request $request)
    {
        $response = $this->payment_for_auth($request);

        return response()->json($response);
    }

    public function update_cart_auth(Request $request)
    {
        $response = $this->update_cart_for_auth($request);

        return response()->json($response);
    }

    public function delete_cart_auth(Request $request)
    {
        $response = $this->delete_cart_for_auth($request);

        return response()->json($response);
    }




    public function remove_all_cart_auth(Request $request)
    {


        $response = $this->remove_all_cart_for_auth($request);

        return response()->json($response);
    }
    public function add_cart_auth(Request $request)
    {


        $response = $this->add_cart_for_auth($request);

        return response()->json($response);
    }

    public function show_cart_auth($userId, Request $request)
    {


        $response = $this->show_cart_for_auth($userId);

        return response()->json($response);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = $this->add_cart($request);
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

    public function show_nofi()
    {
        $response = $this->all_nofi();

        return response()->json($response);
    }

    public function update_nofi()
    {
        $response = $this->update_all_nofi();

        return response()->json($response);
    }
}
