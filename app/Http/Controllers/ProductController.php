<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {

        $variants = Variant::with(['product_variant' => function ($query) {
            $query->select('variant_id', 'variant')->groupBy('variant_id', 'variant');
        }])->get();

        $query = Product::with(['variants' => function ($q) {
            $q->with('variant_price');
        }]);



        if (request()->has('search_title') && request('search_title') !== null) {
            $key = request()->search_title;
            $query->where(function ($q) use ($key) {
                return $q->Where('title', $key)
                    ->orWhere('title', 'LIKE', '%' . $key . '%');
            });
        }

        if(request()->has('variant') && request('variant') !== null) {
            $variant_query = request('variant');
            $query->with(['variants' => function ($q) use ($variant_query) {
                $q->where('variant', $variant_query);
            }]);
        }

        if (request()->has('date') && request('date') !== null) {
            $from_date = request('date');
            $query->whereBetween('created_at', [$from_date, Carbon::now()]);
        }

        if (request()->has('price_to') && request()->has('price_from') && request('price_to') !== null && request('price_from') !== null) {
            $key_from = request()->price_from;
            $key_to = request()->price_to;


            $query->with(['variants' => function ($q) use ($key_from, $key_to) {
                $q->whereHas('variant_price', function ($query) use ($key_from, $key_to) {
                    $query->whereBetween('price', [$key_from, $key_to]);
                })->with(['variant_price']);
            }]);
        }

        $products = $query->with(['variants' => function ($q) {
            $q->with('variant_price');
        }])->paginate(5);

        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
