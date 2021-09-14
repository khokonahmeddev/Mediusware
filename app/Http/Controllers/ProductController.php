<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $variant;
    
    public function index()
    {
        $products = Product::with('productVariants', 'productVariantPrice')
            ->when(request()->get('title'), function (Builder $builder) {
                $builder->where('title', 'LIKE', '%' . request()->get('title') . '%');
            })->when(request()->get('variant'), function (Builder $builder) {
                $builder->whereHas('productVariants', function (Builder $builder) {
                    $builder->where('variant', 'LIKE', '%' . request()->get('variant') . '%');
                });
            })->when(request()->get('price_from'), function (Builder $builder) {
                $builder->whereHas('productVariantPrice', function (Builder $builder) {
                    $builder->whereBetween('price', [request()->get('price_from'), request()->get('price_to')]);
                });
            })->when(request()->get('date'), function (Builder $builder) {
                $builder->whereDate('created_at', '=', Carbon::parse(request()->get('date'))->format('Y-m-d'));
            })->paginate(5);

        $variantItems = Variant::with('productVariants')->get();
        $groups = $variantItems->groupBy('title');

        return view('products.index', compact('products', 'variantItems', 'groups'));
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


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:products,title|max:255',
            'sku' => 'required|unique:products,sku|max:255',
        ]);

        $product = Product::create($request->only('title', 'sku', 'description'));
        if ($request->product_variant) {
            foreach ($request->product_variant as $key => $items) {
                foreach ($items['tags'] as $tag) {
                    $this->variant = $product->productVariants()->updateOrCreate([
                        'variant' => $tag,
                        'variant_id' => $items['option'],
                    ]);
                }

                foreach ($request->product_variant_prices as $item) {
                    $product->productVariantPrice()->updateOrCreate([
                        'product_variant_one' => $this->variant->id,
                        'product_variant_two' => $this->variant->id,
                        'product_variant_three' => $this->variant->id,
                        'price' => $item['price'],
                        'stock' => $item['stock'],
                    ]);
                }

            }


        }
        return redirect()->back()->with('message', 'Product created successfully');

    }


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
