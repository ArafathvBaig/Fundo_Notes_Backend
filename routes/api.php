<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LabelController;
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'api'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resetPassword', [ForgotPasswordController::class, 'resetPassword']);

    Route::post('createNote', [NoteController::class, 'createNote']);
    Route::get('displayNoteById', [NoteController::class, 'displayNoteById']);
    Route::get('displayNotes', [NoteController::class, 'displayAllNotes']);
    Route::post('updateNoteById', [NoteController::class, 'updateNoteById']);
    Route::post('deleteNoteById', [NoteController::class, 'deleteNoteById']);
    Route::post('addNoteLabel', [NoteController::class, 'addNoteLabel']);
    Route::post('deleteNoteLabel', [NoteController::class, 'deleteNoteLabel']);
    Route::post('pinNoteById', [NoteController::class, 'pinNoteById']);
    Route::post('unPinNoteById', [NoteController::class, 'unPinNoteById']);
    Route::get('getAllPinnedNotes', [NoteController::class, 'getAllPinnedNotes']);
    Route::post('archiveNoteById', [NoteController::class, 'archiveNoteById']);
    Route::post('unArchiveNoteById', [NoteController::class, 'unArchiveNoteById']);
    Route::get('getAllArchivedNotes', [NoteController::class, 'getAllArchivedNotes']);
    Route::post('colourNoteById', [NoteController::class, 'colourNoteById']);

    Route::post('createLabel', [LabelController::class, 'createLabel']);
    Route::get('readAllLabels', [LabelController::class, 'readAllLabels']);
    Route::post('updateLabel', [LabelController::class, 'updateLabel']);
    Route::post('deleteLabel', [LabelController::class, 'deleteLabel']);
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('getUser', [UserController::class, 'get_user']);
});