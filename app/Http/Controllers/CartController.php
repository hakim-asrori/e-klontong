<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cart, $cartItem, $product;

    public function __construct(Cart $cart, CartItem $cartItem, Product $product)
    {
        $this->cart = $cart;
        $this->product = $product;
        $this->cartItem = $cartItem;
    }

    public function index(Request $request)
    {
        $query = $this->cart->query();
        $query->selectRaw('carts.*, users.name, users.phone, users.email')
            ->leftJoin("users", "users.id", "=", "carts.user_id");

        if ($request->has('user_id')) {
            $query->where('carts.user_id', $request->user_id);
        }

        $countCart = $query->count();
        $carts = $query->paginate($request->per_page);

        if ($request->has('user_id')) {
            $carts->getCollection()->transform(function ($cart) use ($request) {
                $cartItemQuery = $cart->cartItems()
                    ->newQuery()
                    ->selectRaw('cart_items.id, products.name, cart_items.quantity, products.price, products.weight, cart_items.is_checked, product_images.path AS image, COALESCE(products.price * cart_items.quantity, 0) AS total_price, COALESCE(products.weight * cart_items.quantity, 0) AS total_weight');

                if ($request->has('delivery')) {
                    $cartItemQuery->where(function ($cartItemQuery) use ($request) {
                        $cartItemQuery->whereHas('product.deliveryService', function ($cartItemQuery) use ($request) {
                            $cartItemQuery->where('type', $request->delivery);
                        });
                    });
                }

                if ($request->has('checked')) {
                    $cartItemQuery->where('is_checked', 1);
                }

                $items = $cartItemQuery
                    ->leftJoin("products", "products.id", "=", "cart_items.product_id")
                    ->leftJoin("product_images", "products.id", "=", "product_images.product_id")
                    ->get();

                foreach ($items as &$item) {
                    $item->image = url(Storage::url($item->image));
                }

                $cart->items = $items;

                return $cart;
            });

            return $this->detail($carts->items());
        }

        return MessageFixer::render(
            code: $countCart > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countCart > 0 ? null : "Category no available.",
            data: $countCart > 0 ? $carts->items() : null,
            paginate: ($carts instanceof LengthAwarePaginator) && $countCart > 0  ? [
                "current_page" => $carts->currentPage(),
                "last_page" => $carts->lastPage(),
                "total" => $carts->total(),
                "from" => $carts->firstItem(),
                "to" => $carts->lastItem(),
            ] : null
        );
    }

    protected function detail($cart)
    {
        return MessageFixer::render(
            code: count($cart) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($cart) > 0 ? null : "Cart no available.",
            data: count($cart) > 0 ? $cart[0] : null
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        try {
            $cart = $this->cart->firstOrCreate([
                'user_id' => Auth::user()->id,
            ]);

            $cartItems = $cart->cartItems()->where('product_id', $request->product_id)->get();
            if ($cartItems->count() > 0) {
                foreach ($cartItems as $item) {
                    $item->update([
                        'quantity' => $item->quantity + 1
                    ]);
                }
            } else {
                $cart->cartItems()->create([
                    'product_id' => $request->product_id
                ]);
            }

            DB::commit();
            return MessageFixer::success(message: "Product has been saved to cart");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|numeric|min:1',
            'type' => 'in:plus,minus'
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $cartItem = $this->cartItem->find($request->item_id);

        try {
            if ($request->has('type')) {
                if ($request->type == 'plus') {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + 1
                    ]);
                } else {
                    if ($cartItem->quantity == 1) {
                        $cartItem->delete();
                    } else {
                        $cartItem->update([
                            'quantity' => $cartItem->quantity - 1
                        ]);
                    }
                }
            } else {
                $cartItem->update([
                    'quantity' => $request->quantity,
                    'is_checked' => $cartItem->is_checked == 1 ? 0 : 1
                ]);
            }

            DB::commit();
            return MessageFixer::success(message: "Quantity has been updated");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:cart_items,id'
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $cartItem = $this->cartItem->find($request->item_id);

        try {
            $cartItem->delete();

            DB::commit();
            return MessageFixer::success(message: "Cart item has been deleted");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
