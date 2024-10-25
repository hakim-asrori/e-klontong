<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Tables\Columns\Order\CustomReference;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->formatStateUsing(function (Order $order) {
                        $service = $order->delivery_service == 1 ? "Laut" : "Udara";
                        return "$order->reference - $service";
                    })
                    ->description(function (Order $order) {
                        return new HtmlString("
                        {$order->name}
                        <a href='https://api.whatsapp.com/send?phone=$order->phone&text=' target='_blank'>$order->phone</a>
                        <br> <span title='$order->address'>" . Str::limit($order->address, 30) . "</span>
                    ");
                    })->html()
                    ->searchable(),
                TextColumn::make('orderItems.product.name')->listWithLineBreaks()->bulleted(),
                TextColumn::make('total')->money('IDR'),
                SelectColumn::make('status')
                    ->options(OrderStatusEnum::all())
                    ->rules(['required'])
                    ->afterStateUpdated(function ($state, $record) {
                        Notification::make()
                            ->title('Update status successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->filters([
                SelectFilter::make("status")
                    ->multiple()
                    ->options(OrderStatusEnum::all()),
                SelectFilter::make("product")
                    ->relationship("orderItems.product", "name")
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('reference')
                    ->formatStateUsing(function (Order $order) {
                        $service = $order->delivery_service == 1 ? "Laut" : "Udara";
                        return "$order->reference - $service";
                    }),
                TextEntry::make('status')
                    ->formatStateUsing(function (Order $order) {
                        return OrderStatusEnum::show($order->status);
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        "1" => 'gray',
                        "2" => 'warning',
                        "3" => 'info',
                        "4" => 'success',
                    }),
                TextEntry::make('name')->label('Personal Information')->formatStateUsing(function (Order $order) {
                    return new HtmlString("
                        {$order->name}<br>
                        <a href='https://api.whatsapp.com/send?phone=$order->phone&text=' target='_blank'>$order->phone</a>
                        <br> <span title='$order->address'>" . $order->address . "</span>");
                }),
                TextEntry::make('total')->money('IDR'),
                TextEntry::make('orderItems.product.name')
                    ->listWithLineBreaks()
                    ->bulleted(),
                TextEntry::make('created_at')->label('Order At')->since(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\InvoiceOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
