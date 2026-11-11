<?php

Route::namespace('Lambda\Agent\Controllers')
    ->prefix('agent')
    ->group(function ($router) {
        $router->get('/', 'AgentController@index');
        $router->get('users', 'AgentController@getUsers');
        $router->get('deletedusers', 'AgentController@getDeletedUsers');
        $router->get('users/show/{id}', 'AgentController@show')->middleware('jwt');
        $router->post('users/create', 'AgentController@store');
        $router->post('users/upload', 'AgentController@upload')->middleware('jwt');
        $router->patch('users/store/{id}', 'AgentController@update');
        $router->delete('users/destroy/{id}', 'AgentController@destroy');
        $router->delete('users/forceDestroy/{id}', 'AgentController@forceDelete');
        $router->get('users/restore/{id}', 'AgentController@restore');
        $router->get('wizard', 'AgentController@wizard')->middleware(['jwt', 'web']);
        // wizard/create and profile/{id} used to point at Lambda\Agent\Controllers\ProfileController,
        // a class that does not exist anywhere in this package's history — every hit was a 500 and
        // `php artisan route:list` in host apps died on the reflection. Removed until a real
        // controller exists (lambda-builder12 src/modules/agent/views/wizard.vue still posts to it).
    });

// Authenticating
Route::namespace('Lambda\Agent\Controllers')
    ->prefix('auth')
    ->group(function ($router) {
        $router->match(['get', 'post'], 'login', 'AuthController@login');
        $router->get('/', 'AuthController@login');
        $router->get('/{any}', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        $router->get('me', 'AuthController@me');
        $router->post('password-reset', 'PasswordController@passwordReset');
        $router->post('send-forgot-mail', 'PasswordController@sendMail')->name('sendMail');
    });

// `/pages` (PagesController) and `auth/check` (RoleController@checkRoleAndRedirect) were removed:
// neither controller exists anywhere in this package, so the routes only ever 500ed and broke
// `php artisan route:list` in host apps.

//Agent routes
Route::namespace('Lambda\Agent\Controllers')
    ->prefix('agent')
    ->group(function ($router) {
        $router->get('/users/{type?}', 'AgentController@getUsers');
        $router->get('/user/{id}', 'AgentController@getUser');
        $router->get('/delete/{id}', 'AgentController@deleteUser');
        $router->get('/delete/complete/{id}', 'AgentController@deleteUserComplete');
        $router->get('/restore/{id}', 'AgentController@restoreUser');
        $router->get('/search/{q?}', 'AgentController@searchUsers');
        $router->get('/roles', 'AgentController@getRoles');
    });
