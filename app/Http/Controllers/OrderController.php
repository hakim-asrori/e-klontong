<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $order, $cartItem, $product;

    public function __construct(Order $order, CartItem $cartItem, Product $product)
    {
        $this->order = $order;
        $this->cartItem = $cartItem;
        $this->product = $product;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = $this->order->query();
        $query->selectRaw('orders.*, users.name, users.phone, users.email')
            ->leftJoin("users", "users.id", "=", "orders.user_id");

        if ($request->has('id')) {
            $query->where('orders.id', $request->id);
        }

        if ($request->has('status')) {
            $query->where('orders.status', $request->status);
        }

        $query->where('user_id', $user->id);

        $countOrder = $query->count();
        $orders = $query->paginate($request->per_page);

        $orders->getCollection()->transform(function ($order) {
            $items = $order->orderItems()
                ->selectRaw('order_items.id, products.name, order_items.quantity, products.price, products.weight, COALESCE(products.weight * order_items.quantity, 0) AS total_weight, product_images.path AS image, products.slug')
                ->leftJoin("products", "products.id", "=", "order_items.product_id")
                ->leftJoin("product_images", "products.id", "=", "product_images.product_id")
                ->get();

            foreach ($items as &$item) {
                $item->image = url(Storage::url($item->image));
            }

            $order->items = $items;

            return $order;
        });

        if ($request->has('id')) {
            return $this->detail($orders->items());
        }

        return MessageFixer::render(
            code: $countOrder > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countOrder > 0 ? null : "Order no available.",
            data: $countOrder > 0 ? $orders->items() : null,
            paginate: ($orders instanceof LengthAwarePaginator) && $countOrder > 0  ? [
                "current_page" => $orders->currentPage(),
                "last_page" => $orders->lastPage(),
                "total" => $orders->total(),
                "from" => $orders->firstItem(),
                "to" => $orders->lastItem(),
            ] : null
        );
    }

    protected function detail($order)
    {
        return MessageFixer::render(
            code: count($order) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($order) > 0 ? null : "Order no available.",
            data: count($order) > 0 ? $order[0] : null
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array',
            'item_ids.*' => 'required|exists:cart_items,id',
            'service' => 'required|in:1,2',
            'address_id' => 'required|exists:new_addresses,id',
            'address' => 'required|max:150',
            'name' => 'required|max:150',
            'phone' => 'required|max:15',
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $reference = "INV/" . date("Y-m/") . mt_rand(000000, 999999);

        try {
            $order = $this->order->create([
                'user_id' => Auth::user()->id,
                'reference' => $reference,
                'delivery_service' => $request->service,
                'address' => $request->address,
                'name' => $request->name,
                'phone' => $request->phone,
            ]);

            $totalPrice = 0;
            $totalWeightGr = 0;
            $totalWeightKg = 0;
            $cartItems = [];
            foreach ($request->item_ids as $id) {
                $cartItem = $this->cartItem->selectRaw('cart_items.id, cart_items.product_id, products.name, cart_items.quantity, products.price, products.weight, products.weight_type, COALESCE(products.price * cart_items.quantity, 0) AS total_price, COALESCE(products.weight * cart_items.quantity, 0) AS total_weight')
                    ->leftJoin('products', 'products.id', '=', 'cart_items.product_id')
                    ->find($id);
                $totalPrice += $cartItem->total_price;
                if ($cartItem->weight_type == 1) {
                    $totalWeightGr += $cartItem->total_weight;
                } else {
                    $totalWeightKg += $cartItem->total_weight;
                }
                $cartItems[] = $cartItem;

                $order->orderItems()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price
                ]);

                $cartItem->delete();
            }

            $order->update([
                'total' => $totalPrice,
                'total_weight' => json_encode(["kg" => $totalWeightKg, "gram" => $totalWeightGr])
            ]);

            DB::commit();
            return MessageFixer::render(
                message: "Order successfully",
                data: $this->formatMessage($order, $cartItems, $request)
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
            return MessageFixer::error($th->getMessage());
        }
    }

    public function cancel(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            "order_id" => "required|numeric|integer",
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $order = $this->order->find($request->order_id);

        if ($order->status > $this->order::ORDER) {
            return MessageFixer::render(code: MessageFixer::WARNING_PROCESS, message: 'Orders cannot be cancelled, because your order has already been processed', data: $order);
        }

        try {
            $order->update([
                "status" => $this->order::CANCEL
            ]);

            DB::commit();
            return MessageFixer::success(
                message: "Order has been cancelled",
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function exportPdf($id)
    {
        $record = $this->order->findOrFail($id);

        return view('filament.resources.order-resource.pages.invoice-pdf', compact('record'));
    }

    protected function formatMessage($order, $cartItems, $request)
    {
        $messages = array();
        $messages[] = "## Pesanan " . $order->reference . " ##";
        $messages[] = "";
        foreach ($cartItems as $item) {
            $messages[] = $item->name;
            $messages[] = $item->quantity . " x " . number_format($item->price, 0, ",", ".") . str_pad(" ", 3, " ", STR_PAD_LEFT) . number_format($item->total_price, 0, ",", ".");
        }
        $messages[] = "";
        $messages[] = "Total Pembelian: " . number_format($order->total, 0, ",", ".");
        $messages[] = "Total Berat: " . json_decode($order->total_weight);
        $messages[] = "Order Via: " . $request->service == 2 ? "Udara" : "Laut";

        $implodeMessage = implode("\r\n", $messages);
        $implodeMessage = urlencode($implodeMessage);

        return "https://api.whatsapp.com/send?phone=819066621593&text=$implodeMessage";
    }
}
