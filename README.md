# Filament File Explorer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/michaeld555/filament-explorer.svg?style=flat-square)](https://packagist.org/packages/michaeld555/filament-explorer)
[![Total Downloads](https://img.shields.io/packagist/dt/michaeld555/filament-explorer.svg?style=flat-square)](https://packagist.org/packages/michaeld555/filament-explorer)

## Overview

**Filament File Explorer** is a file explorer and media browser that includes an integrated code editor. It allows you to access and manage server files directly from the Filament panel.

> ⚠ **Warning**
>
> This package is intended for **super-admins only**. It provides unrestricted access to all files and directories on the server and **should not** be made available to regular users.

---

## 📥 Installation

To install the package, run the following command in your terminal:

```bash
composer require michaeld555/filament-explorer
```

After installation, run the setup command:

```bash
php artisan filament-explorer:install
```

Then, register the plugin in your panel provider file located at `/app/Providers/Filament/YourPanelProvider.php`:

```php
->plugin(
    \Michaeld555\FilamentExplorer\FilamentExplorerPlugin::make()
        ->hiddenFolders([
            base_path('app')
        ])
        ->hiddenFiles([
            base_path('.env')
        ])
        ->hiddenExtensions([
            "php"
        ])
        ->allowCreateFolder()
        ->allowEditFile()
        ->allowCreateNewFile()
        ->allowRenameFile()
        ->allowDeleteFile()
        ->allowMarkdown()
        ->allowCode()
        ->allowPreview()
        ->hideFromPanel() // Optionally, hide menu navigation from the panel
        ->basePath(base_path())
)
```

## 📦 Publishing Assets

If you need to customize some configurations, you can publish the package files using the following commands:

### 📄 Configuration file:
```bash
php artisan vendor:publish --tag="filament-explorer-config"
```

### 🎨 View files:
```bash
php artisan vendor:publish --tag="filament-explorer-views"
```

### 🌍 Language files:
```bash
php artisan vendor:publish --tag="filament-explorer-lang"
```

---

## 📜 Changelog

See the [CHANGELOG](CHANGELOG.md) for more details on recent changes.

---

## 👥 Credits

- [Michael Douglas](https://github.com/michaeld555)
- [TomatoPHP](https://github.com/tomatophp)
- [All Contributors](../../contributors)

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE.md](LICENSE.md) file for more details.
