<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(true)->maxLength(50),
                TextInput::make('description')->required(true)->maxLength(150),
                Select::make('type')->options([
                    1 => 'Default',
                    2 => 'Menu',
                    3 => 'Banner',
                ]),
                FileUpload::make('image')->required(true)
                    ->directory('categories/' . date('Y/Ym/Ymd'))
                    ->image()
                    ->maxSize(2048),
                ToggleButtons::make("enable_home")->label("Appears on the Home Page?")->boolean()->grouped()->icons([
                    true => "heroicon-o-check",
                    false => "heroicon-o-x-mark",
                ])->required()->default(false)->live(),
                Select::make('direction')->label('Format Layout')->options([
                    1 => 'Horizontal',
                    0 => 'Vertical',
                ])->required()->visible(fn(callable $get) => $get('enable_home')),
                TextInput::make('per_page')->required(true)->minLength(0)->numeric()->maxLength(10)->default(0)->visible(fn(callable $get) => $get('enable_home')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('type')->formatStateUsing(function (string $state) {
                    switch ($state) {
                        case 1:
                            return 'Default';
                            break;
                        case 2:
                            return 'Menu';
                            break;
                        case 3:
                            return 'Banner';
                            break;

                        default:
                            return 'Default';
                            break;
                    }
                }),
                TextColumn::make('enable_home')->label("Appears on the Home Page?")->formatStateUsing(function ($state) {
                    return $state ? 'Active' : 'Non Active';
                }),
                ToggleColumn::make('status')
                    ->afterStateUpdated(function ($state, $record) {
                        Notification::make()
                            ->title('Update status successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make("status")
                    ->options([
                        1 => 'Active',
                        0 => 'Non Active'
                    ]),
                SelectFilter::make("type")
                    ->options([
                        1 => 'Default',
                        2 => 'Menu',
                        3 => 'Banner',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
