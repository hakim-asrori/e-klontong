<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();

        try {
            $record->update(array_merge($data, ['slug' => Str::slug($data['name'] . "-" . Str::random(6))]));

            $record->deliveryService()->update([
                'type' => $data['type']
            ]);

            DB::commit();
            return $record;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
