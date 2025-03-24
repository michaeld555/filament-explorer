<?php

namespace Michaeld555\FilamentExplorer;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Michaeld555\FilamentExplorer\Pages\Explorer;

class FilamentExplorerPlugin implements Plugin
{

    public function getId(): string
    {
        return 'filament-explorer';
    }

    public bool $allowUpload = false;

    public bool $allowCreateNewFile = false;

    public bool $allowCreateFolder = false;

    public bool $allowDeleteFile = false;

    public bool $allowRenameFile = false;

    public bool $allowEditFile = false;

    public bool $allowMarkdown = false;

    public bool $allowCode = false;

    public bool $allowPreview = true;

    public bool $hideFromPanel = false;

    public array $hiddenFiles = [];

    public array $hiddenExtantions = [];

    public array $hiddenFolders = [];

    public string $basePath = '';

    public function hiddenFiles(array $files): static
    {
        $this->hiddenFiles = $files;
        return $this;
    }

    public function hiddenExtantions(array $extantions): static
    {
        $this->hiddenExtantions = $extantions;
        return $this;
    }

    public function hiddenFolders(array $folders): static
    {
        $this->hiddenFolders = $folders;
        return $this;
    }

    public function allowRenameFile(bool $condation = true): static
    {
        $this->allowRenameFile = $condation;
        return $this;
    }

    public function allowDeleteFile(bool $condation = true): static
    {
        $this->allowDeleteFile = $condation;
        return $this;
    }

    public function allowUpload(bool $condation = true): static
    {
        $this->allowUpload = $condation;
        return $this;
    }

    public function allowCreateNewFile(bool $condation = true): static
    {
        $this->allowCreateNewFile = $condation;
        return $this;
    }

    public function allowCreateFolder(bool $condation = true): static
    {
        $this->allowCreateFolder = $condation;
        return $this;
    }

    public function allowEditFile(bool $condation = true): static
    {
        $this->allowEditFile = $condation;
        return $this;
    }

    public function allowMarkdown(bool $condation = true): static
    {
        $this->allowMarkdown = $condation;
        return $this;
    }

    public function allowCode(bool $condation = true): static
    {
        $this->allowCode = $condation;
        return $this;
    }

    public function allowPreview(bool $condation = true): static
    {
        $this->allowPreview = $condation;
        return $this;
    }

    public function hideFromPanel(bool $condation = false): static
    {
        $this->hideFromPanel = $condation;
        return $this;
    }

    public function basePath(string $path): static
    {
        $this->basePath = $path;
        return $this;
    }

    public function register(Panel $panel): void
    {

        $panel->pages([
            Explorer::class
        ]);

    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }

}
