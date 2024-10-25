@php
    use App\Enums\OrderStatusEnum;
    use App\Enums\DeliveryServiceEnum;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&amp;display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            font-family: "Inter", 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
    </style>
</head>

<body class="bg-slate-300">
    <div
        class="sm:w-6/12 w-screen print:w-full mx-auto border h-full pt-10 print:border-none print:pt-0  bg-white rounded-lg">
        <div class="w-full flex justify-center mb-5">
            <img src="{{ asset('img/logo/logo.png') }}" alt="" height="150" width="150">
        </div>

        <div class="w-full p-6">
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
                    <thead class="bg-white dark:text-white text-dark border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider border-e">
                                Item
                            </th>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider border-e">
                                Qty
                            </th>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider">
                                Subtotal
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:text-white text-dark divide-y divide-white">
                        @foreach ($record->orderItems as $item)
                            <tr class=" border-b">
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
                    <tfoot class="bg-white dark:text-white text-dark divide-y divide-white">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider border-e" colspan="2">
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
    </div>
</body>

</html>

@if (request()->has('print') && request('print') == 'true')
    <script>
        window.print()
    </script>
@endif
