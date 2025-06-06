<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\RelationManagers\AuthorsRelationManager;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Contracts\Service\Attribute\Required;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->description('Crie um post para você')
                ->schema([
                    
                    TextInput::make('title')
                        ->label('Título')
                        ->rules('min:3')
                        ->required(),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord:true),
                    Select::make('category_id')
                        ->required()
                        ->label('Categoria')
                        ->relationship("category", "name")
                        ->searchable(),
                    ColorPicker::make('color')
                        ->required(),
                    MarkdownEditor::make('content')
                        ->required()->columnSpanFull(),
                ])->columnSpan(1)->columns(2),
                Group::make()->schema([

                    Section::make("Image")
                    ->collapsible()
                    ->schema([
                        FileUpload::make('thumbnail')
                         ->disk('public')
                         ->directory('thumbnails'),               
                         ])->columnSpan(1),
                    Section::make("Meta")
                    ->schema([
                        
                        TagsInput::make('tags')
                         ->required(),
                        Checkbox::make('published')
                         ->required(),
                    ]),
                    // Section::make("Authors")
                    // ->schema([
                    //    Select::make('authors')
                    //    ->relationship('authors', 'name')
                    // ])
                ])
            ])->columns([
                'default' => 1,
                'md' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                 ->sortable()
                 ->searchable()
                 ->toggleable(isToggledHiddenByDefault:true),
                ImageColumn::make('thumbnail')
                 ->circular(),
                TextColumn::make('title')
                 ->sortable()
                 ->searchable()
                  ->toggleable(),
                TextColumn::make('slug')
                 ->sortable()
                 ->searchable()
                  ->toggleable(),
                TextColumn::make('category.name')
                 ->sortable()
                 ->searchable()
                  ->toggleable(),
                ColorColumn::make('color')
                 ->toggleable(),
                TextColumn::make('content')
                 ->sortable()
                 ->searchable()
                  ->toggleable(),
                TextColumn::make('tags')
                 ->toggleable(),
                CheckboxColumn::make('published')
                 ->toggleable(),
                TextColumn::make('created_at')
                 ->label('Publicado em')
                 ->date()
                 ->sortable()
                 ->searchable()
                 ->toggleable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            AuthorsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
