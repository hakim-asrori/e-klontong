<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;

class InvoiceOrder extends Page
{
    use InteractsWithRecord;

    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.invoice-order';

    protected function getHeaderActions(): array
    {
        return [
            Action::make("back")
                ->url(fn(): string => route('filament.siteman.resources.orders.index')),
            Action::make("export")
                ->color("gray")
                ->url(fn(): string => url("export/pdf/order/" . $this->record->id . "?print=true"))
                ->openUrlInNewTab()
        ];
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record)->load(["user", "orderItems.product"]);
    }

    // public function render(): View
    // {
    //     return view('filament.resources.order-resource.pages.invoice-order', [
    //         'record' => $this->record
    //     ]);
    // }
}
