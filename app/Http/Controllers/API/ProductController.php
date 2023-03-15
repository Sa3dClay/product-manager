<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());

        return response(['products' => ProductResource::collection($user->products)]);
    }

    public function show()
    {
        // code
    }

    public function store(CreateProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail(Auth::id());

            $product = $user->products()->create([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            $images = $request->file('images');

            foreach ($images as $image) {
                $imagePath = $image->store('products', ['disk' => 'public']);

                $product->images()->create([
                    'image_path' => $imagePath,
                ]);
            }

            DB::commit();

            return response(['product' => new ProductResource($product)], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response([
                'message' => 'something went wrong while creating product',
                'error' => $e,
            ], 422);
        }
    }

    public function update()
    {
        // code
    }

    public function destroy()
    {
        // code
    }
}
