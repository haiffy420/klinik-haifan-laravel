<?php

use App\Filament\Resources\UserResource;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::middleware(['VerifyIsAdmin'])->group(function () {
//     Filament::resource('users', UserResource::class);
// });

Route::get('artisan-command/{command}', function($command) {
    Artisan::call($command);
});
Route::get('/linkstorage', function () {
    $targetFolder = base_path().'/storage/app/public';
    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/storage';
    // symlink($targetFolder, $linkFolder);
    if (@symlink($targetFolder, $linkFolder)) { // The @ suppresses the error
        echo 'Symlink process successfully completed';
    } else {
        echo 'Symlink process failed';
        return 1; // You should probably exit with a non-zero status code if that is your whole script.
}
});
