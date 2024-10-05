<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();

        try {
            $product = static::getModel()::create(array_merge($data, ['slug' => Str::slug($data['name'] . "-" . Str::random(6))]));
            $product->deliveryService()->create([
                'type' => $data['type']
            ]);

            DB::commit();
            return $product;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
