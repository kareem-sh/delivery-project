<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFavoriteRequest;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(CreateFavoriteRequest $request)
    {
        if($request->user()->role=="user")
        {
            Favorite::create([
                'user_id'=>$request->user()->id,
                'product_id'=>$request->product_id
            ]);
        }
    }
    public function show(Request $request)
    {
        if($request->user()->role=="user")
        {
            $user=$request->user();
            $favorites=$user->products;
            return $favorites;
        }
    }

    public function destroy(Request $request,$id)
    {
        $favorite=Favorite::find($id);
        $user_id=$favorite->user_id;
        if($request->user()->role=="user"&&$request->user()->id==$user_id)
        {
            $favorite->delete();
        }
    }
}
