<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                ->schema([
                    
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->rules('min:3')
                        ->required(),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord:true),
                    Forms\Components\Select::make('category_id')
                        ->required()
                        ->label('Categoria')
                        ->relationship("category", "name")
                        ->searchable(),
                    Forms\Components\ColorPicker::make('color')
                        ->required(),
                    Forms\Components\MarkdownEditor::make('content')
                        ->required()->columnSpanFull(),
                ])->columnSpan(1)->columns(2),
                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make("Image")
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                         ->disk('public')
                         ->directory('thumbnails'),               
                         ])->columnSpan(1),
                    Forms\Components\Section::make("Meta")
                    ->schema([
                        
                        Forms\Components\TagsInput::make('tags')
                         ->required(),
                        Forms\Components\Checkbox::make('published')
                         ->required(),
                    ])
                ])
            ])->columns([
                'default' => 1,
                'md' => 2,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\CheckboxColumn::make('published')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
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
}
