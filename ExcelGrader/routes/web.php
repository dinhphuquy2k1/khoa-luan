<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamBankController;
use App\Http\Controllers\PhpSpreadsheetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExamManagerController;
use App\Http\Controllers\ExamEvaluatorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashBoardController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('Home');
});

Route::resource('department', DepartmentController::class);
Route::resource('exambank', ExamBankController::class);
Route::resource('api/exam-manager', ExamManagerController::class);
Route::resource('api/exam-grader', PhpSpreadsheetController::class);
Route::resource('api/user', UserController::class);
Route::resource('api/dash-board', DashBoardController::class);
Route::prefix('api/user')->group(function () {
    Route::post('/login', [UserController::class,'login']);
    Route::post('/checkpassword', [UserController::class,'checkpassword']);
    Route::post('/checkEmailExits', [UserController::class,'checkEmailExits']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [UserController::class,'logout']);
    });
});

Route::prefix('api/exambank')->group(function () {
    Route::post('/updateExambank', [ExamBankController::class,'updateExambank']);
    Route::get('/getExamBankSetting', [ExamBankController::class,'getExamBankSetting']);
    Route::post('/getCriteriaByExambankId', [ExamBankController::class,'getCriteriaByExambankId']);
    Route::post('/deleteCriteria', [ExamBankController::class,'deleteCriteria']);
});

Route::prefix('api/exam-manager')->group(function () {
    Route::post('/update-examshift', [ExamManagerController::class,'updateExamShift']);
    Route::post('/insert-examshift', [ExamManagerController::class,'insertExamShift']);
    Route::post('/update-exammanager', [ExamManagerController::class,'updateExamManager']);
    Route::get('/get-examshift/{id}', [ExamManagerController::class,'getExamShift']);
    Route::get('/delete-examshift/{id}', [ExamManagerController::class,'deleteExamShift']);
});

Route::prefix('api/exam-evaluator')->group(function () {
    Route::post('/export', [ExamEvaluatorController::class,'exportExamResult']);
    Route::post('/exportExamList', [ExamEvaluatorController::class,'exportExamList']);
});


Route::resource('api/exam-evaluator', ExamEvaluatorController::class);

Route::post('/api/saveCriteria',[PhpSpreadsheetController::class,'saveCriteria']);
Route::post('/api/exam-manager/checkExamShiftCodeExits',[ExamManagerController::class,'checkExamShiftCodeExits']);
Route::post('/api/exam-evaluator/uploadFile',[ExamEvaluatorController::class,'uploadFile'])->name('uploadFile');


Route::get('/{any?}', function () {
    return view('Home');
})->where('any', '.*$');


