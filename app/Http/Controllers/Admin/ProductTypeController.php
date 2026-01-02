<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producttype;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{



        public function post_product_type(Request $request)
        {
            $type =Producttype::create(
                [
                    'name' => $request->name
                ]
            );
            return response()->json([
                'success' => true,
                'id' => $type->id,
                 'name' => $type->name]);
        }

}
