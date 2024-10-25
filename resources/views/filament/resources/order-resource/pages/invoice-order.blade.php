@php
    use App\Enums\OrderStatusEnum;
    use App\Enums\DeliveryServiceEnum;
@endphp
<x-filament-panels::page>
    <div class="w-full mx-auto p-6 dark:bg-gray-900 bg-gray-200 rounded-lg">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h2 class="text-xl font-bold">Invoice</h2>
                <p>#{{ $record->reference }}</p>
            </div>
            <div class="text-right">
                <p><strong>Issue Date:</strong> {{ date('Y-m-d', strtotime($record->created_at)) }}</p>
                <p><strong>Delivery Service:</strong> {{ DeliveryServiceEnum::show($record->status) }}</p>
                <p><strong>Status:</strong> {{ OrderStatusEnum::show($record->status) }}</p>
            </div>
        </div>

        <div class="mb-5">
            <div class="mb-4">
                <p><strong>Bill From:</strong></p>
                <p>Kenshuu Express</p>
                <p>819066621593</p>
            </div>
            <div class="mb-4">
                <p><strong>Bill To:</strong></p>
                <p>{{ $record->name }}</p>
                <p>{{ $record->user->email }}</p>
                <p>{{ $record->phone }}</p>
            </div>
        </div>

        <div class="overflow-x-auto mb-5">
            <table class="w-full divide-y divide-white border">
                <thead class="bg-gray-900 dark:text-white text-dark">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider border-e">
                            Item
                        </th>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider border-e">
                            Qty
                        </th>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider">
                            Subtotal
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-900 dark:text-white text-dark divide-y divide-white">
                    @foreach ($record->orderItems as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-medium border-e">
                                {{ $item->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap border-e">{{ $item->quantity }} Item
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ Number::currency($item->quantity * $item->price, 'IDR', 'id') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-900 dark:text-white text-dark divide-y divide-white">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider border-e" colspan="2">
                            Total
                        </th>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ Number::currency($record->total, 'IDR', 'id') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mb-5">
            <p><strong>Address</strong></p>
            <p>{{ $record->address }}</p>
        </div>
    </div>
</x-filament-panels::page>
