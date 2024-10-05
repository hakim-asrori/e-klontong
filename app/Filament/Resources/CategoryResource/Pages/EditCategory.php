<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

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
            if ($record->image != $data['image']) {
                Storage::disk('public')->delete($record->image);
            }

            $record->update(array_merge($data, ['slug' => Str::slug($data['name'] . "-" . Str::random(6))]));

            DB::commit();
            return $record;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
