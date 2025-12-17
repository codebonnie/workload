<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// ? Auth
$routes->addRedirect('/', '/login');
$routes->get('/login', 'Auth\Login::index', ['as' => 'login']);
$routes->post('/login', 'Auth\Login::index');
$routes->get('/logout', 'Auth\Login::logout', ['as' => 'logout']);

$routes->get('/refresh/captcha', 'Auth\Login::refreshCaptcha', ['as' => 'refresh.captcha']);

// ? Profile
$routes->get('/profile', 'User\Profile::index', ['as' => 'user.profile']);
$routes->post('/profile', 'User\Profile::index');

// ? Dashboard
$routes->get('/dashboard', 'Dashboard::index', ['as' => 'dashboard']);
$routes->get('/dashboard/summary', 'Dashboard::getProjectSummary', ['as' => 'dashboard.summary']);
$routes->get('/dashboard/getProjectCounts', 'User\ProjectController::getProjectCounts', ['as' => 'dashboard.projectCounts']);
$routes->get('/dashboard/list-projects', 'Dashboard::listProjects', ['as' => 'dashboard.list-projects']);

// ? Branch
$routes->get('/unit-kerja', 'User\UnitKerjaController::index', ['as' => 'unit-kerja.index']);
// ? Branch API
$routes->post('/optionsDivOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsDivOnly');
$routes->post('/optionsCabOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsCabOnly');
$routes->post('/optionsDirOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsDirOnly');
$routes->post('/options/unitKerjaAPI', 'User\UnitKerjaController::options');
$routes->get('/dataTables/unitKerjaAPI', 'User\UnitKerjaController::dataTables');
$routes->post('/post/unitKerjaAPI', 'User\UnitKerjaController::post');
$routes->post('/edit/unitKerjaAPI', 'User\UnitKerjaController::edit');
$routes->post('/delete/unitKerjaAPI', 'User\UnitKerjaController::delete');

// ? Role
$routes->get('/role', 'User\RoleController::index', ['as' => 'role.index']);
// ? Role API
$routes->post('/options/roleAPI', 'User\RoleController::options');
$routes->get('/dataTables/roleAPI', 'User\RoleController::dataTables');
$routes->post('/post/roleAPI', 'User\RoleController::post');
$routes->post('/edit/roleAPI', 'User\RoleController::edit');
$routes->post('/delete/roleAPI', 'User\RoleController::delete');
$routes->get('/permission/roleAPI/(:num)', 'User\RoleController::getPermissions/$1');
$routes->put('/assignPermission/roleAPI', 'User\RoleController::assignPermission');

// ? Permission
$routes->get('/permission', 'User\PermissionController::index', ['as' => 'permission.index']);
// ? Permission API
$routes->post('/options/permissionAPI', 'User\PermissionController::options');
$routes->get('/dataTables/permissionAPI', 'User\PermissionController::dataTables');
$routes->post('/post/permissionAPI', 'User\PermissionController::post');
$routes->post('/edit/permissionAPI', 'User\PermissionController::edit');
$routes->post('/delete/permissionAPI', 'User\PermissionController::delete');

// ? User
$routes->get('/user', 'User\UserController::index', ['as' => 'user.index']);

// ? User API
$routes->get('/dataTables/userAPI', 'User\UserController::dataTables');
$routes->post('/post/userAPI', 'User\UserController::post');
$routes->post('/edit/userAPI', 'User\UserController::edit');
$routes->post('/updateStatus/userAPI', 'User\UserController::updateStatus');
$routes->post('/delete/userAPI', 'User\UserController::delete');
$routes->post('/resetPassword/userAPI', 'User\UserController::resetPassword');

// ? Log Viewer
$routes->get('/log/error', 'Log\LogError::index', ['as' => 'log.error']);
$routes->get('/log/activity', 'Log\LogActivityController::index', ['as' => 'log.activity']);
// ? Log Activity API
$routes->get('/dataTables/logActivityAPI', 'Log\LogActivityController::dataTables');
$routes->get('/show/logActivityAPI/(:num)', 'Log\LogActivityController::showLog/$1');
// ? Log Histori 
$routes->get('/log/histori', 'Log\HistoriController::index', ['as' => 'log.histori']);
$routes->get('/dataTables/historiAPI', 'Log\HistoriController::dataTables',);
$routes->get('/show/historiAPI/(:num)', 'Log\HistoriController::showHistori/$1');
$routes->get('/stats/historiAPI', 'Log\HistoriController::getProjectStats');


// ? Request Update User
$routes->get('/request', 'User\ReqUpdateUserController::index', ['as' => 'request.index']);
// ? API Request Update User
$routes->get('/dataTables/requestAPI', 'User\ReqUpdateUserController::dataTables');
$routes->post('/post/requestAPI', 'User\ReqUpdateUserController::post');
$routes->post('/getPdf/requestAPI', 'User\ReqUpdateUserController::getPdf');
$routes->post('/approval/requestAPI', 'User\ReqUpdateUserController::approval');

// ? Request projects
$routes->get('/Project', 'User\ProjectController::index');
// ? API Function Project
$routes->get('/dataTables/Project', 'User\ProjectController::dataTables');
$routes->post('/post/Project', 'User\ProjectController::post');
$routes->post('/edit/Project', 'User\ProjectController::edit');
$routes->post('/delete/Project', 'User\ProjectController::delete');
$routes->post('/progress/Project', 'User\ProjectController::progress');
$routes->post('updateDetail/Project', 'User\ProjectController::updateDetail');
$routes->get('getDetail/Project', 'User\ProjectController::getDetail');
$routes->get('downloadDocument/Project/(:segment)/(:segment)', 'User\ProjectController::downloadDocument/$1/$2');
$routes->post('/export/Project', 'User\ProjectController::export');
$routes->get('getHistori/Project(:num)', 'User\ProjectController::getHistori/$1');
$routes->post('/UrlDocs/Project', 'User\ProjectController::UrlDocs');
$routes->post('/generateNumber/Project', 'User\ProjectController::generateNumber');

//? Request of Onhands
$routes->get('/Onhands', 'User\OnhandsController::index');
//? API Function project
$routes->get('/dataTables/Onhands', 'User\OnhandsController::dataTables');
$routes->post('/get/Onhands', 'User\OnhandsController::get');
$routes->post('/post/Onhands', 'User\OnhandsController::post');
$routes->post('/edit/Onhands', 'User\OnhandsController::edit');
$routes->post('/delete/Onhands', 'User\OnhandsController::delete');
$routes->post('/progress/Onhands', 'User\OnhandsController::progress');
$routes->post('/generateNumber/Onhands', 'User\OnhandsController::generateNumber');
$routes->post('/getDocuments/Onhands', 'User\OnhandsController::getDocuments');
$routes->get('/downloadDocument/Onhands', 'User\OnhandsController::downloadDocument');
$routes->get('/exportToExcel/Onhands', 'User\OnhandsController::exportToExcel');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
