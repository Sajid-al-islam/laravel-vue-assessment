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
            $query->rightJoin('product_variants','product_variants.product_id','=','products.id')
            ->select('products.*','product_variants.variant')
            ->where('variant', $variant_query);

            // $query->with(['variants' => function ($q) use ($variant_query) {
            //     $q->where('variant', $variant_query);
            // }]);
        }

        if (request()->has('date') && request('date') !== null) {
            $from_date = request('date');
            $query->whereBetween('created_at', [$from_date, Carbon::now()]);
        }

        if (request()->has('price_to') && request()->has('price_from') && request('price_to') !== null && request('price_from') !== null) {
            $key_from = request()->price_from;
            $key_to = request()->price_to;

            $query->rightJoin('product_variant_prices','product_variant_prices.product_id','=','products.id')
            ->select('products.*','product_variant_prices.price')
            ->whereBetween('price', [$key_from, $key_to]);
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

        $product = new Product();
        $product->title = request()->title;
        $product->sku = request()->sku;
        $product->description = request()->description;
        $product->save();
        $request_product_variants = request()->product_variant;
        $product_variant_prices = request()->product_variant_prices;

        foreach($product_variant_prices as $key => $item) {

            $single_variants = explode("/",$item['title']);

            foreach ($single_variants as $key => $variant_single) {
                $product_variant = new ProductVariant();
                $product_variant->variant = $variant_single;
                foreach($request_product_variants as $varint_item) {
                    $product_variant->variant_id = $varint_item['option'];
                }
                $product_variant->product_id = $product->id;
                $product_variant->save();

                $variant_price_query = new ProductVariantPrice();
                if($key == 0) {
                    $variant_price_query->product_variant_one = $product_variant->id;
                }
                if($key == 1) {
                    $variant_price_query->product_variant_two = $product_variant->id;
                }
                if($key == 2) {
                    $variant_price_query->product_variant_three = $product_variant->id;
                }
                $variant_price_query->price = $item['price'];
                $variant_price_query->stock = $item['stock'];
                $variant_price_query->product_id = $product->id;
                $variant_price_query->save();
            }


            // $product_variant->save();
        }


        return response()->json('success', 200);
        // $product_variant_prices = collect(request()->product_variant_prices);
        // foreach ($product_variant_prices as $key => $value) {
        //     $single_variants = explode("/",$value['title']);
        //     foreach ($single_variants as $key => $variant_single) {
        //         if(strlen($variant_single) > 0) {
        //             $product_variants = $product_variants->flatten(1);
        //             dd($product_variants);
        //         }
        //     }
        //     // if(strlen())
        //     dd($single_variants);
        //     $product_variant_price = new ProductVariantPrice();
        // }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
        $product = Product::where('id', $product)->with('variants')->first();

        return response()->json($product);
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
    public function update(Request $request, $product)
    {
        $product = Product::find($product);
        $product->title = request()->title;
        $product->sku = request()->sku;
        $product->description = request()->description;
        $product->save();
        $request_product_variants = request()->product_variant;
        $product_variant_prices = request()->product_variant_prices;


        foreach($product_variant_prices as $key => $item) {

            $single_variants = explode("/",$item['title']);

            foreach ($single_variants as $key => $variant_single) {
                $product_variant = new ProductVariant();
                $product_variant->variant = $variant_single;
                foreach($request_product_variants as $varint_item) {
                    $product_variant->variant_id = $varint_item['option'];
                }
                $product_variant->product_id = $product->id;
                $product_variant->save();

                $variant_price_query = new ProductVariantPrice();
                if($key == 0) {
                    $variant_price_query->product_variant_one = $product_variant->id;
                }
                if($key == 1) {
                    $variant_price_query->product_variant_two = $product_variant->id;
                }
                if($key == 2) {
                    $variant_price_query->product_variant_three = $product_variant->id;
                }
                $variant_price_query->price = $item['price'];
                $variant_price_query->stock = $item['stock'];
                $variant_price_query->product_id = $product->id;
                $variant_price_query->save();
            }

        }


        return response()->json('success', 200);

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
