<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\AddImagesToProductRequest;

class ProductController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());

        return response(['products' => ProductResource::collection($user->products)]);
    }

    public function show(Product $product)
    {
        return response(['product' => new ProductResource($product)]);
    }

    public function uploadProductImagesToPublicDisk($product, $images)
    {
        foreach ($images as $image) {
            $imagePath = $image->store('products', ['disk' => 'public']);

            $product->images()->create([
                'image_path' => $imagePath,
            ]);
        }
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

            $images = $request->file('images') ?? [];

            $this->uploadProductImagesToPublicDisk($product, $images);

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

    public function addImages(AddImagesToProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);

            $images = $request->file('images');

            $this->uploadProductImagesToPublicDisk($product, $images);

            DB::commit();

            return response(['product' => new ProductResource($product)]);
        } catch (\Exception $e) {
            DB::rollback();

            return response([
                'message' => 'something went wrong while uploading image',
                'error' => $e,
            ], 422);
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->all());

        return response(['product' => new ProductResource($product)]);
    }

    public function destroy(Product $product)
    {
        try {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $product->delete();

            return response(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response([
                'message' => 'Something went wrong while deleting product!',
                'error' => $e,
            ], 422);
        }
    }

    public function deleteImage(ProductImage $image)
    {
        try {
            Storage::disk('public')->delete($image->image_path);

            $image->delete();

            return response(['message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            return response([
                'message' => 'Something went wrong while deleting image!',
                'error' => $e,
            ], 422);
        }
    }
}
