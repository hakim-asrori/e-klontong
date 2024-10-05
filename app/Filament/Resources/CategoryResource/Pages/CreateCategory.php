<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();

        try {
            $category = static::getModel()::create(array_merge($data, ['slug' => Str::slug($data['name'] . "-" . Str::random(6))]));

            DB::commit();
            return $category;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
