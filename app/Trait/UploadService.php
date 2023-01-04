<?php

namespace App\Trait;

use App\Models\Category;
use App\Models\Product;
use App\Models\Product_images;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Str;

trait UploadService
{


    public function upload_image($request_file)
    {
        // dd($request_file);


        $path = $request_file
            ->storeAs('public', Str::random(2) . time() . $request_file
                ->getClientOriginalName(), "s3");


        $link_path = Storage::disk('s3')->url($path);

        return   $link_path;

        // $images = Image::create([
        //     "filename" => $path,
        //     "url" =>     $path

        // ]);
    }


    public function delete_s3($url)
    {

        $url_from_database = $url;
        $url_decode = url(urldecode($url_from_database));
        $short_url = strstr($url_decode, "/public");
        // if (Storage::disk('s3')->exists($short_url)) {
        // dd("co $short_url");
        Storage::disk('s3')->delete($short_url);
        // } else {
        //     dd("không tìm thấy anh để xóa trong s3");
        // }
    }
}
