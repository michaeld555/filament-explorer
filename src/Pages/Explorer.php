<?php

namespace Michaeld555\FilamentExplorer\Pages;

use Creagia\FilamentCodeField\CodeField;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Michaeld555\FilamentExplorer\Models\Files;

class Explorer extends Page implements HasTable
{

    use InteractsWithTable;

    public string $language = "php";

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static string $view = 'filament-explorer::explorer';

    protected static ?string $slug = 'files-explorer';

    public static function getNavigationLabel(): string
    {
        return trans('filament-explorer::messages.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-explorer::messages.group');
    }

    public function getTitle(): string
    {
        return trans('filament-explorer::messages.title');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return !filament('filament-explorer')->hideFromPanel;
    }

    #[On('refreshTable')]
    public function refreshTable()
    {

        $this->dispatch('$refresh');

        $this->resetTableFiltersForm();

    }

    public function table(Table $table): Table
    {

        return $table
            ->columns([

                TextColumn::make('name')
                    ->label(trans('filament-explorer::messages.files.columns.name'))
                    ->searchable()
                    ->sortable()

            ])
            ->headerActions([

                \Filament\Tables\Actions\Action::make('create')
                    ->hidden(fn() => !filament('filament-explorer')->allowCreateNewFile)
                    ->label(trans('filament-explorer::messages.actions.create'))
                    ->icon('heroicon-o-plus')
                    ->form(function () {

                        $type = [
                            'file-code' => trans('filament-explorer::messages.types.code'),
                            'file-markdown' => trans('filament-explorer::messages.types.markdown'),
                        ];

                        if (filament('filament-explorer')->allowCreateFolder) {
                            $type['folder'] = trans('filament-explorer::messages.types.folder');
                        }

                        if (filament('filament-explorer')->allowUpload) {
                            $type['upload'] = trans('filament-explorer::messages.types.upload');
                        }

                        return [

                            Select::make('type')
                                ->label(trans('filament-explorer::messages.create.type'))
                                ->columnSpanFull()
                                ->required()
                                ->default('file-code')
                                ->searchable()
                                ->options([
                                    'upload' => trans('filament-explorer::messages.types.upload'),
                                    'file-code' => trans('filament-explorer::messages.types.code'),
                                    'file-markdown' => trans('filament-explorer::messages.types.markdown'),
                                    'folder' => trans('filament-explorer::messages.types.folder'),
                                ])
                                ->live(),

                            TextInput::make('name')
                                ->label(trans('filament-explorer::messages.create.name'))
                                ->required()
                                ->hidden(fn(Get $get) => $get('type') == 'upload'),

                            Select::make('extension')
                                ->label(trans('filament-explorer::messages.create.extension'))
                                ->required()
                                ->searchable()
                                ->options([
                                    'php' => 'PHP',
                                    'css' => 'CSS',
                                    'sass' => 'SASS',
                                    'json' => 'JSON',
                                    'js' => 'JS',
                                    'ts' => 'TS',
                                    'vue' => 'Vue',
                                    'env' => 'ENV',
                                    'yaml' => 'YAML',
                                    'xml' => 'XML',
                                    'txt' => 'TXT',
                                    'html' => 'HTML',
                                    'blade' => 'BLADE',
                                    'log' => 'LOG',
                                    'md' => 'MD',
                                ])
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {

                                    $this->language = $get('extension');

                                    if ($get('extension') === 'php') {
                                        $set('code', '<?php');
                                    }

                                })
                                ->hidden(fn(Get $get) => $get('type') != 'file-code'),

                            FileUpload::make('file')
                                ->label(trans('filament-explorer::messages.create.file'))
                                ->columnSpanFull()
                                ->required()
                                ->preserveFilenames()
                                ->hidden(fn(Get $get) => $get('type') != 'upload'),

                            CodeField::make('code')
                                ->label(trans('filament-explorer::messages.create.code'))
                                ->columnSpanFull()
                                ->required()
                                ->view('filament-explorer::components.code')
                                ->setLanguage($this->language ?? 'php')
                                ->hidden(fn(Get $get) => $get('type') != 'file-code'),

                            MarkdownEditor::make('markdown')
                                ->label(trans('filament-explorer::messages.create.markdown'))
                                ->columnSpanFull()
                                ->required()
                                ->hidden(fn(Get $get) => $get('type') != 'file-markdown'),

                        ];

                    })
                    ->action(function (array $data) {

                        $path = session()->has('filament-explorer-path') ? session()->get('filament-explorer-path') : (filament('filament-explorer')->basePath ?: config('filament-explorer.start_path'));

                        if ($data['type'] === 'upload') {

                            $exists = File::exists($path . '/' . $data['file']);

                            if ($exists) {

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();

                                return;

                            } else {

                                File::copy(storage_path('app/public/' . $data['file']), $path . '/' . $data['file']);

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.uploaded'))
                                    ->success()
                                    ->send();

                            }

                            File::delete(storage_path('app/public/' . $data['file']));

                        } elseif ($data['type'] === 'file-code') {

                            $exists = File::exists($path . "/{$data['name']}.{$data['extension']}");

                            if ($exists) {

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();

                                return;

                            }

                            File::put($path . "/{$data['name']}.{$data['extension']}", $data['code']);

                            Notification::make()
                                ->title(trans('filament-explorer::messages.notifications.saved'))
                                ->success()
                                ->send();

                        } elseif ($data['type'] === 'file-markdown') {

                            $exists = File::exists($path . "/{$data['name']}.md");

                            if ($exists) {

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.file-exists'))
                                    ->danger()
                                    ->send();

                                return;

                            }

                            File::put($path . "/{$data['name']}.md", $data['markdown']);

                            Notification::make()
                                ->title(trans('filament-explorer::messages.notifications.saved'))
                                ->success()
                                ->send();

                        } elseif ($data['type'] === 'folder') {

                            $exists = File::exists($path . "/{$data['name']}");

                            if ($exists) {

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.folder-exists'))
                                    ->danger()
                                    ->send();

                                return;

                            }

                            File::makeDirectory($path . "/{$data['name']}");

                            Notification::make()
                                ->title(trans('filament-explorer::messages.notifications.created'))
                                ->success()
                                ->send();

                        }

                        $this->dispatch('refreshTable');

                    }),

                \Filament\Tables\Actions\Action::make('home')
                    ->label(trans('filament-explorer::messages.actions.home'))
                    ->icon('heroicon-o-home')
                    ->color('info')
                    ->action(function () {

                        session()->forget('filament-explorer-path');

                        session()->forget('filament-explorer-current');

                        $this->dispatch('refreshTable');

                    }),

                \Filament\Tables\Actions\Action::make('back')
                    ->label(trans('filament-explorer::messages.actions.back'))
                    ->icon('heroicon-o-chevron-left')
                    ->color('warning')
                    ->hidden(fn() => !session()->has('filament-explorer-current') || count(json_decode(session()->get('filament-explorer-current'))) < 1)
                    ->action(function () {

                        $history = collect(json_decode(session()->get('filament-explorer-current')))->last();

                        $historyAfter = collect(json_decode(session()->get('filament-explorer-current')))->filter(fn($item) => $item != $history);

                        session()->put('filament-explorer-current', json_encode($historyAfter->toArray()));

                        session()->put('filament-explorer-path', $historyAfter->last());

                        $this->dispatch('refreshTable');

                    }),

            ])
            ->content(fn() => view('filament-explorer::table.content'))
            ->paginated(false)
            ->query(Files::query());

    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getFolderAction(?Files $file = null)
    {

        return Action::make('getFolderAction')
            ->view('filament-explorer::actions.folder', ['file' => $file])
            ->action(function (array $arguments) {

                $currentArray = session()->has('filament-explorer-current') ? json_decode(session()->get('filament-explorer-current')) : [];

                $currentArray[] = $arguments['file']['path'];

                session()->put('filament-explorer-current', json_encode($currentArray));

                session()->put('filament-explorer-path', $arguments['file']['path']);

                $this->dispatch('refreshTable');

            });

    }

    public function getFileAction(?Files $file = null)
    {

        return Action::make('getFileAction')
            ->label(function (array $arguments) {

                return $arguments['file']['name'];

            })
            ->fillForm(function (array $arguments) {

                return [
                    'content' => (str($arguments['file']['extension'])->contains([
                        "php",
                        "json",
                        "js",
                        "yaml",
                        "xml",
                        "lock",
                        "txt",
                        "html",
                        "log",
                        "md",
                    ]) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension'])) ? File::get($arguments['file']['path']) : $arguments['file'],
                ];

            })
            ->form(function (array $arguments) {

                return ((str($arguments['file']['extension'])->contains([
                    "php",
                    "json",
                    "js",
                    "yaml",
                    "xml",
                    "lock",
                    "txt",
                    "html",
                    "log",
                ]) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension'])) ? [

                    CodeField::make('content')
                        ->disabled(fn() => !filament('filament-explorer')->allowEditFile)
                        ->label(trans('filament-explorer::messages.edit.content'))
                        ->view('filament-explorer::components.code')
                        ->setLanguage($arguments['file']['extension']),

                ] : (str($arguments['file']['extension'])->contains('md') ? [MarkdownEditor::make('content')->label(trans('filament-explorer::messages.edit.content'))->disabled(fn() => !filament('filament-explorer')->allowEditFile)] : []));

            })
            ->extraModalFooterActions(function (array $arguments, Action $action) {

                return [

                    Action::make('deleteFile')
                        ->hidden(fn() => !filament('filament-explorer')->allowDeleteFile)
                        ->label(trans('filament-explorer::messages.actions.delete'))
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->cancelParentActions()
                        ->action(function () use ($arguments, $action) {

                            File::delete($arguments['file']['path']);

                            Notification::make()
                                ->title(trans('filament-explorer::messages.notifications.deleted'))
                                ->success()
                                ->send();

                            $this->dispatch('refreshTable');

                        }),

                    Action::make('rename_file')
                        ->hidden(fn() => !filament('filament-explorer')->allowRenameFile)
                        ->label(trans('filament-explorer::messages.actions.rename'))
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square')
                        ->requiresConfirmation()
                        ->cancelParentActions()
                        ->form([

                            TextInput::make('name')
                                ->label(trans('filament-explorer::messages.files.columns.name'))

                        ])
                        ->fillForm([
                            "name" => $arguments['file']['name']
                        ])
                        ->action(function (array $data) use ($arguments, $action) {

                            $exists = File::exists($arguments['file']['path']);

                            if ($exists) {

                                File::move($arguments['file']['path'], str_replace($arguments['file']['name'], $data['name'], $arguments['file']['path']));

                                Notification::make()
                                    ->title(trans('filament-explorer::messages.notifications.renamed'))
                                    ->success()
                                    ->send();

                            }

                            $this->dispatch('refreshTable');

                        }),

                ];

            })
            ->infolist(function (array $arguments) {

                File::deleteDirectory(storage_path('app/public/tmp'));

                return filament('filament-explorer')->allowPreview ? ((str($arguments['file']['extension'])->contains([
                    "php",
                    "json",
                    "js",
                    "yaml",
                    "xml",
                    "lock",
                    "txt",
                    "html",
                    "log",
                    "md",
                ])) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension']) ? [] : [

                    TextEntry::make('content')
                        ->hidden(fn() => !filament('filament-explorer')->allowPreview)
                        ->label(trans('filament-explorer::messages.edit.content'))
                        ->view('filament-explorer::preview.file', ['file' => $arguments['file']])

                ]) : [];

            })
            ->action(function (array $arguments, array $data) {

                if (filament('filament-explorer')->allowEditFile) {

                    (str($arguments['file']['extension'])->contains([
                        "php",
                        "json",
                        "js",
                        "yaml",
                        "xml",
                        "lock",
                        "txt",
                        "html",
                        "log",
                        "md",
                    ])) || str($arguments['file']['name'])->contains(['.env', '.git', '.editor']) || empty($arguments['file']['extension']) ? File::put($arguments['file']['path'], $data['content']) : null;

                    if (isset($data) && isset($data['content'])) {

                        Notification::make()
                            ->title(trans('filament-explorer::messages.notifications.saved'))
                            ->success()
                            ->send();

                    }

                }

            })
            ->view('filament-explorer::actions.file', ['file' => $file]);
    }

}
