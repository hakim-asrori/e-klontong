<?php

namespace App\Filament\Resources;

use App\Enums\DeliveryServiceEnum;
use App\Enums\WeightParamEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(true)->maxLength(150),
                TextInput::make('price')->required(true)->numeric(),
                TextInput::make('weight')->required(true)->numeric(),
                Select::make('weight_type')->options(WeightParamEnum::all())
                    ->required(),
                Textarea::make('description')->required(true)->maxLength(255),
                Select::make('type')->options(function (?Product $product, Get $get, Set $set) {
                    if (!empty($product->name)) {
                        $set('type', $product->deliveryService->type);
                    }

                    return DeliveryServiceEnum::all();
                })
                    ->label('Delivery Service')
                    ->required(),
                Select::make('category')->required()->multiple()->relationship('categories', 'name')->preload()->searchable(),
                Repeater::make('image')
                    ->relationship('image')  // Relasi hasMany dengan tabel images
                    ->schema([
                        FileUpload::make('path')
                            ->directory('products/' . date('Y/Ym/Ymd'))
                            ->image()
                            ->maxSize(2048)
                            ->label('Upload Image'),
                    ])
                    ->columns(1)
                    ->minItems(1)
                    ->maxItems(1) // Minimum 1 gambar yang harus ada
                    ->required(fn($record) => $record === null)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('price')->money('IDR'),
                ImageColumn::make('image.path'),
                TextColumn::make('categories.name')->listWithLineBreaks()->bulleted(),
                ToggleColumn::make('status')
                    ->afterStateUpdated(function ($state, $record) {
                        Notification::make()
                            ->title('Update status successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->filters([
                SelectFilter::make("status")
                    ->options([
                        1 => 'Active',
                        0 => 'Non Active'
                    ]),
                SelectFilter::make("category")
                    ->relationship("categories", "name")
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
