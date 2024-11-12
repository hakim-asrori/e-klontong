<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryServiceEnum;
use App\Enums\WeightParamEnum;
use App\Facades\MessageFixer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $product, $category;

    public function __construct(Product $product, Category $category)
    {
        $this->product = $product;
        $this->category = $category;
    }

    public function index(Request $request)
    {
        $query = $this->product->query()->selectRaw("products.*, IFNULL((SELECT SUM(order_items.quantity) FROM order_items WHERE order_items.product_id = products.id), 0) AS total_sales");

        if ($request->has('search')) {
            $query->where("name", "LIKE", "%$request->search%");
        }

        if ($request->has('status')) {
            if ($request->status == "true") {
                $query->where("status", 1);
            }

            if ($request->status == "false") {
                $query->where("status", 0);
            }
        }

        if ($request->has('category')) {
            $query->whereHas('categories', function ($query) use ($request) {
                $query->where('slug', $request->category);
                $query->orWhere('categories.id', $request->category);
            });
        }

        if ($request->has('delivery_service')) {
            $query->whereHas('deliveryService', function ($query) use ($request) {
                $query->where('type', $request->delivery_service);
            });
        }

        if ($request->has('product_id')) {
            $query->where('id', $request->product_id);
        }

        if ($request->has('slug')) {
            $query->where('slug', $request->slug);
        }

        $query->orderBy(request('sort_by', 'products.id'), request('sort_direction', 'asc'));

        $countProduct = $query->count();
        $products = $query->paginate($request->per_page);

        $products->getCollection()->transform(function ($product) {
            $image = url(Storage::url($product->image->path));
            $categories = $product->categories->pluck("name");
            $deliveryService = DeliveryServiceEnum::show($product->deliveryService->type);

            unset($product->image, $product->categories, $product->deliveryService);

            $product->image = $image;
            $product->categories = $categories;
            $product->delivery_service = $deliveryService;
            $product->weight_type = WeightParamEnum::show($product->weight_type);

            return $product;
        });

        if ($request->has('product_id') || $request->has('slug')) {
            return $this->detail($products->items());
        }

        return MessageFixer::render(
            code: $countProduct > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countProduct > 0 ? null : "Product no available.",
            data: $countProduct > 0 ? $products->items() : null,
            paginate: ($products instanceof LengthAwarePaginator) && $countProduct > 0  ? [
                "current_page" => $products->currentPage(),
                "last_page" => $products->lastPage(),
                "total" => $products->total(),
                "from" => $products->firstItem(),
                "to" => $products->lastItem(),
            ] : null
        );
    }

    protected function detail($product)
    {
        return MessageFixer::render(
            code: count($product) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($product) > 0 ? null : "Product no available.",
            data: count($product) > 0 ? $product[0] : null
        );
    }
}
