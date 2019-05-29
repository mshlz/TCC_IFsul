<?php

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

#Route::get('/', ['as'=> '/','uses'=>'Controller@homepage']);
Route::get('/dashboard', ['as'=> 'dashboard','uses'=>'Controller@dashboard']);



#==========================================================================

#--------------------------------------------------------------------------
// routes to auth
#--------------------------------------------------------------------------


Route::get('/login', ['as'=>'/login','uses'=>'Controller@loginRequester']);

Route::post('/auth', ['as'=> 'auth' ,'uses'=>'DashboardController@auth']);


Route::post('/logout', ['as'=> 'requester.logout' ,'uses'=>'DashboardController@logout']);

Route::get('/index', ['as'=> 'index' ,'uses'=>'DashboardController@index']);



#==========================================================================


#Route::get('1', function () {
 #   return view('welcome');
#});*/
