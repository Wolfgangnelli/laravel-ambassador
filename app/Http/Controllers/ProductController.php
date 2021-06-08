<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->hasAny('title', 'price', 'imege')) {
            $product = Product::create($request->only('title', 'description', 'price', 'image'));
        }

        return response($product, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->only('title', 'description', 'price', 'image'));

        return response($product, Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Return the products for the frontend and then i will paginate it
     */
    public function frontend()
    {
        // get the products from redis cache if they exist on it, so i don't query the db
        if ($products = \Cache::get('products_frontend')) {
            return $products;
        }

        //sleep(2);
        $products = Product::all();
        Cache::set('products_frontend', $products, 30 * 60); // 30 minuts (30 * 60seconds)

        return $products;
    }

    public function backend(Request $request)
    {
        //clear the cache
        //Cache::forget('products_backend');

        $page = $request->input('page', 1);
        $ttl = 30 * 60;

        $products = collect(Cache::remember('products_backend', $ttl, fn () => Product::all()));
        $totalProducts = $products->count();
        $lastPage = ceil($totalProducts / 9);

        //manually paginate using collections
        return [
            'data' => $products->forPage($page, 9)->values(),
            'meta' => [
                'total' => $totalProducts,
                'page' => $page,
                'last_page' => $lastPage
            ]
        ];
    }
}
