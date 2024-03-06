<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

//ACCOUNTING ROUTER
Route::post('accounting/uploadAccountingInfo', 'Accounting\AccountingController@uploadAccountingInfo');
Route::post('accounting/deleteLastUpload', 'Accounting\AccountingController@deleteLastUpload');

//ACCOUNTING ROUTER

Route::resource('users', 'User\UserController');
Route::resource('companies', 'Company\CompanyController', ['except' => 'create', 'edit']);
Route::resource('locations', 'Location\LocationController', ['except' => 'create', 'edit']);
Route::resource('banks', 'Bank\BankController', ['except' => 'create', 'edit']);
Route::resource('accounts', 'Account\AccountController', ['except' => 'create', 'edit']);
Route::resource('mapFiles', 'MapFile\MapFileController', ['except' => 'create', 'edit']);
Route::resource('usersCompanies', 'User\UserCompanyController');

//CONCILIAR ROUTES
Route::resource('conciliar', 'Conciliar\ConciliarController')->only(
	[
		'index',
		'uploadIniFile',
		'setIniProcess',
		'isIniConciliar',
		'closeIniConciliar',
		'uploadAccountFile',
		'getCuentasToConciliar',
		'uploadConciliarContable'
	]
);
Route::name('getCuentasToConciliar')
	->get('conciliar/getCuentasToConciliar', 'Conciliar\ConciliarController@getCuentasToConciliar');
Route::name('setIniProcess')
	->post('conciliar/setIniProcess', 'Conciliar\ConciliarController@setIniProcess');
Route::name('isIniConciliar')
	->post('conciliar/isIniConciliar', 'Conciliar\ConciliarController@isIniConciliar');
Route::name('closeIniConciliar')
	->post('conciliar/closeIniConciliar', 'Conciliar\ConciliarController@closeIniConciliar');
Route::name('uploadAccountFile')
	->post('conciliar/uploadAccountFile', 'Conciliar\ConciliarController@uploadAccountFile');
Route::name('uploadIniConciliar')
	->post('conciliar/uploadIniFile', 'Conciliar\ConciliarController@uploadIniFile');
Route::name('uploadConciliarContable')
	->post('conciliar/uploadConciliarContable', 'Conciliar\ConciliarController@uploadConciliarContable');
Route::name('balanceCloseAccount')
	->post('conciliar/balanceCloseAccount', 'Conciliar\ConciliarController@balanceCloseAccount');

//CONCILIAR - HEADER CONTROLLER
Route::resource('headers', 'Conciliar\HeaderController', ['except' => 'create', 'edit']);

//CONCILIAR ROUTES


Route::resource('externalTxType', 'TxType\ExternalTxTypeController', ['except' => 'create', 'edit']);
Route::resource('localTxType', 'TxType\LocalTxTypeController', ['except' => 'create', 'edit']);

Route::resource('balanceOperativo', 'BalanceGeneral\BalanceGeneralController', ['except' => []]);

//RUTAS MIXTAS
Route::resource('companies.locations', 'Company\CompanyLocationController', ['only' => 'index']);
Route::resource('companies.users', 'Company\CompanyUserController', ['only' => ['index', 'update', 'destroy']]);
Route::resource('users.companies', 'User\UserCompanyController', ['only' => ['index']]);


//EXTRA FUNCTIONS
//GET
//Route::name('hola')->get('users/hola', 'User\UserController@hola');
Route::name('newToken')->get('users/newToken/{user}', 'User\UserController@newToken');
Route::name('setcompany')->get('users/setcompany/{user_id}/{company_id}', 'User\UserController@setCompany');
Route::name('countries')->get('locations/countries/{country_id}', 'Location\LocationController@getAllCountries');
Route::name('countries')->get('locations/states/{country_id}', 'Location\LocationController@getAllStates');
Route::name('countries')->get('locations/cities/{state_id}', 'Location\LocationController@getAllCities');
Route::name('mapFileUpload')->get('mapFiles/getMapIndex/{type}', 'MapFile\MapFileController@getMapIndex');
Route::name('userCompanies')->get('users/userCompanies', 'User\UserController@userCompanies');
Route::name('getCompaniesByEmail')
	->get('users/getCompaniesByEmail/{id}', 'User\UserCompanyController@getCompaniesByEmail');
Route::name('formatsExterno')->get('mapFiles/formatsExterno/{id}', 'MapFile\MapFileController@formatsExterno');
Route::name('formatsLocal')->get('mapFiles/formatsLocal/{id}', 'MapFile\MapFileController@formatsLocal');
Route::name('recoveryPssw')->get('users/recoveryPssw/{email}', 'User\UserController@recoveryPssw');
Route::name('setCurrentCompany')->get('users/setCurrentCompany/{company_id}', 'User\UserController@setCurrentCompany');
Route::name('getHeaderItems')->get('headers/getHeaderItems/{headerId}', 'Conciliar\ItemController@getHeaderItems');


//POST
Route::name('verify')->post('users/verify', 'User\UserController@verify');
Route::name('mapFileUpload')->post('mapFiles/upload', 'MapFile\MapFileController@uploadFile');
Route::name('saveMap')->post('mapFiles/saveMap', 'MapFile\MapFileController@saveMap');
Route::name('addCompaniesToUser')->post('userCompany/companiesToUser', 'User\UserCompanyController@companiesToUser');
Route::name('getUserCompanies')->post('userCompany/getUserCompanies', 'User\UserCompanyController@getUserCompanies');
Route::name('setExternalMap')->post('accounts/setMap', 'Account\AccountController@setMap');
Route::name('setLocalMap')->post('companies/setMap', 'Company\CompanyController@setMap');
Route::name('getCompanyInfo')->post('companies/getCompanyInfo', 'Company\CompanyController@getCompanyInfo');

Route::name('isAdmin')->post('users/isAdmin', 'User\UserController@isAdmin');
Route::name('getAccountsByCenter')->post('accounts/getAccountsByCenter/', 'Account\AccountController@getAccountsByCenter');
Route::name('getUserByToken')->post('users/getUserByToken', 'User\UserController@getUserByToken');


Route::name('setUserCurrentCompany')
	->post('users/userCompanies', 'User\UserCompanyController@setUserCurrentCompany');

//BALANCE
Route::name('balanceGeneralCreateTables')
	->post('balanceGeneral/createTables', 'BalanceGeneral\BalanceGeneralController@createTables');
Route::name('balanceGeneralUploadFile')
	->post('balanceGeneral/uploadBalance', 'BalanceGeneral\BalanceGeneralController@uploadBalance');
Route::name('balanceGeneralUploadConvenios')
	->post('balanceGeneral/uploadConvenios', 'BalanceGeneral\BalanceGeneralController@uploadConvenios');
Route::name('balanceGeneralGetBalance')
	->post('balanceGeneral/getBalance', 'BalanceGeneral\BalanceGeneralController@getBalance');
Route::name('balanceGeneraldownloadConvenio')
	->post('balanceGeneral/downloadConvenio', 'BalanceGeneral\BalanceGeneralController@downloadConvenio');
Route::name('balanceGeneraldownloadConvenioResultado')
	->post('balanceGeneral/downloadConvenioResultado', 'BalanceGeneral\BalanceGeneralController@downloadConvenioResultado');
Route::name('balanceGeneraldownloadBalance')
	->post('balanceGeneral/downloadBalance', 'BalanceGeneral\BalanceGeneralController@downloadBalance');
Route::name('balanceGeneraluploadOperativoMaster')
	->post('balanceGeneral/uploadOperativoMaster', 'BalanceGeneral\BalanceGeneralController@uploadOperativoMaster');
Route::name('balanceGeneraluploadConvenioCuentasMaster')
	->post('balanceGeneral/uploadConvenioCuentasMaster', 'BalanceGeneral\BalanceGeneralController@uploadConvenioCuentasMaster');
//CARTERA GET

Route::name('carteraCreateTables')
	->get('cartera/carteraCreateTables', 'Cartera\ConfecarHeaderController@createTables');

//CARTERA POST

Route::name('carteraUploadCONFECAR')
	->post('cartera/uploadCONFECAR', 'Cartera\ConfecarHeaderController@uploadCONFECAR');
// Route::name('setCurrentCompany')->post('users/setCurrentCompany', 'User\UserController@setCurrentCompany');


//permisos POST
Route::name('userSetRolPermission')
	->post('user/setRolPermission', 'User\UserSecurityController@setRolPermission');

Route::name('userRevokeRolPermission')
	->post('user/revokeRolPermission', 'User\UserSecurityController@revokeRolPermission');

Route::name('setUserRol')
	->post('user/setUserRol', 'User\UserSecurityController@setUserRol');

Route::name('revokeUserRol')
	->post('user/revokeUserRol', 'User\UserSecurityController@revokeUserRol');




//permisos GET
Route::name('getUserList')
	->get('user/getUserList/{search}', 'User\UserSecurityController@getUserList');

Route::name('userCrearRol')
	->get('user/crearRol/{name}', 'User\UserSecurityController@addRol');

Route::name('userCrearPermission')
	->get('user/crearPermission/{name}', 'User\UserSecurityController@addPermission');

Route::name('userGetRoles')
	->get('user/getRoles/', 'User\UserSecurityController@getRoles');

Route::name('userGetPermission')
	->get('user/getPermission/{rolName}', 'User\UserSecurityController@getPermission');

Route::name('getUserRoles')
	->get('user/getUserRoles/{userId}', 'User\UserSecurityController@getUserRoles');





// Route::name('verify')->get('users/verify/{token}','User\UserController@verify');
//Oauth

Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');
