<?php

namespace Michaeld555\FilamentExplorer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Sushi\Sushi;

class Files extends Model
{

    use Sushi;

    public function getRows(): array
    {

        $filterFiles = filament('filament-explorer')->hiddenFiles;

        $filterFolders = filament('filament-explorer')->hiddenFolders;

        $filterExtantions = filament('filament-explorer')->hiddenExtantions;

        $root = session()->has('filament-explorer-path') ? session()->get('filament-explorer-path') : (filament('filament-explorer')->basePath?: config('filament-explorer.start_path'));

        $getFolders = File::directories($root, true);

        $getFiles = File::files($root, true);

        $explorer = [];

        foreach ($getFolders as $folder){

            if(!in_array($folder, $filterFolders)){

                $explorer[] = [
                    "name" => str($folder)->remove($root.'/')->toString(),
                    "folder" => $root,
                    "path" => $folder,
                    "type" => "folder",
                    "size" => "0",
                    "extension" => "folder"
                ];

            }

        }
        foreach ($getFiles as $file){

            if((!in_array($file->getRealPath(), $filterFiles)) && (!in_array($file->getExtension(), $filterExtantions))){

                $totalSize = $file->getSize();

                if($totalSize<1000){
                    $totalSize = $totalSize. 'bytes';
                }
                else if($totalSize<100000){
                    $totalSize = ($totalSize/1000). 'KB';
                }
                else if($totalSize<1000000){
                    $totalSize = ($totalSize/1000). 'KB';
                }
                else if($totalSize<1000000000){
                    $totalSize = ($totalSize/1000000). 'MB';
                }
                else if($totalSize>1000000000){
                    $totalSize = ($totalSize/1000000). 'GB';
                }

                $explorer[] = [
                    "name" => $file->getFilename(),
                    "folder" => $root,
                    "path" => $file->getRealPath(),
                    "type" => "file",
                    "size" => $totalSize,
                    "extension" => $file->getExtension() ?? "file",
                ];
            }

        }

        return $explorer;
    }

}
