<?php

namespace Michaeld555\FilamentExplorer\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Michaeld555\FilamentExplorer\Events\ExplorerFileSaved;

class ExplorerController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {

        if ($request->has('folder_path')) {

            $request->validate([
                "folder_path" => "required",
                "folder_name" => "required",
                "type" => "required",
            ]);

            $root = $request->get('folder_path');
            $name = $request->get('folder_name');
            $type = $request->get('type');

        } else if ($request->has('file_path')) {

            $name = $request->get('file_name');
            $setFilePath = $request->get('file_path');
            $root = str_replace(DIRECTORY_SEPARATOR . $name, '', $request->get('file_path'));

        } else {

            $startPath = config('filament-explorer.start_path');
            $root = $startPath;
            $name = $startPath;
            $type = "home";

        }

        if(!str($root)->contains(base_path())){

            return response()->json([
                "error" => true,
                "message" => trans('filament-explorer::messages.notifications.not-allowed')
            ], 403);

        }

        if ($request->has('file_path')) {

            $getFile = File::get($setFilePath);
            $folders =  File::directories($root);
            $files =  File::files($root);
            $foldersArray = [];
            $filesArray = [];

            foreach ($folders as $folder) {

                array_push($foldersArray, [
                    "path" => $folder,
                    "name" => str_replace($root . DIRECTORY_SEPARATOR, '', $folder),
                ]);

            }

            foreach ($files as $file) {

                array_push($filesArray, [
                    "path" => $file->getRealPath(),
                    "name" => str_replace($root . DIRECTORY_SEPARATOR, '', $file),
                ]);

            }

            $exploadName = explode(DIRECTORY_SEPARATOR, $root);

            $count = count($exploadName);

            $setName = $exploadName[$count - 1];

            $ex = File::extension($setFilePath);

            if ($ex === 'webp' || $ex === 'jpg' || $ex === 'png' || $ex === 'svg' || $ex === 'jpeg' || $ex === 'ico' ||  $ex === 'gif' || $ex === 'tif') {

                $imagBase64 = base64_encode($getFile);

                $getFile = $imagBase64;

            }

            return response()->json([
                "folders" => $foldersArray,
                "files" => $filesArray,
                "back_path" => $root,
                "back_name" => $setName,
                "current_path" => $root,
                "file" => $getFile,
                "ex" => $ex,
                "path" => $setFilePath
            ], 200);

        } elseif ($request->has('content')) {

            $filename = $request->get('path');

            $checkIfFileEx = File::exists($filename);

            if ($checkIfFileEx) {

                File::put($filename, $request->get('content'));

                ExplorerFileSaved::dispatch($filename);

                return response()->json([
                    "success" => true,
                    "message" => trans('filament-explorer::messages.notifications.saved')
                ]);

            }

        } else {

            $folders = File::directories($root);

            $files = File::files($root);

            $foldersArray = [];

            $filesArray = [];

            foreach ($folders as $folder) {

                array_push($foldersArray, [
                    "path" => $folder,
                    "name" => str_replace($root . DIRECTORY_SEPARATOR, '', $folder),
                ]);

            }

            foreach ($files as $file) {

                $ex = File::extension($file);

                array_push($filesArray, [
                    "path" => $file->getRealPath(),
                    "name" => str_replace($root . DIRECTORY_SEPARATOR, '', $file),
                    "ex" => $ex
                ]);

            }

            if ($root == base_path()) {

                array_push($filesArray, [
                    "path" => base_path('.env'),
                    "name" => ".env",
                ]);

            }

            $foldersArray = array_filter($foldersArray, function ($folder) {

                $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $folder['path']);

                return !in_array($path, config('filament-explorer.hidden_folders'));

            });

            $filesArray = array_filter($filesArray, function ($file) {

                $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file['path']);

                return !in_array($path, config('filament-explorer.hidden_files'));

            });

            $exploadName = explode(DIRECTORY_SEPARATOR, $root);

            $count = count($exploadName);

            $setName = $exploadName[$count - 2];

            return response()->json([
                "folders" => $foldersArray,
                "files" => $filesArray,
                "back_path" => str_replace(DIRECTORY_SEPARATOR . $name, '', $root),
                "back_name" => $setName,
                "current_path" => $root,
            ], 200);

        }

    }

}
