<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Foods;
use ResponseFormatter;


use App\Http\Controllers\API\FoodController;
class FoodController extends Controller
{
    //

    // get all foods
    public function all(Reqeust $request){
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        // ordering by price from
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        
        // jika id ada isinya
        if($id){
            // cari food berdasarkan id
            $food = Foods::find($id);
            // jika food ditemukan
            if($food){
                return ResponseFormatter::success($food, "data produk berhasil di ambil");
            }
            else {
                return ResponseFormatter::error(
                    null, "data produk tidak ditemukan", 404
                );
            }
        }

        // jika ada parametr price from dll
        $food = Food::query();
        if($name){
            $food->where('name','like','%'. $name . '%');
        }
        if($type){
            $food->where('type','like','%'. $type . '%');
        }
        if($price_from){
            $food->where('price','>=',$price_from);
        }
        if($price_to){
            $food->where('price','<=',$price_to);
        }
        if($rate_from){
            $food->where('rate','>=',$rate_from);
        }
        if($rate_to){
            $food->where('rate','<=',$rate_to);
        }

        return ResponseFormatter::success($food->paginate($limit), "Data list produk berhasil diambil");

    }
}
