<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\NexoPOS\Services\Validation;
use Modules\NexoPOS\Fields\UnitsGroupsFields;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([ 'tendoo.cors', 'tendoo.auth' ])->group( function() {
    Route::prefix( 'api/nexopos/v4' )->group( function() {
        foreach([ '', '/store/{id}/' ] as $prefix ) {
            Route::prefix( $prefix )->group( function() {
                include_once( dirname( __FILE__ ) . '/api/categories.php' );    
                include_once( dirname( __FILE__ ) . '/api/customers.php' );
                include_once( dirname( __FILE__ ) . '/api/expenses.php' );
                include_once( dirname( __FILE__ ) . '/api/orders.php' );
                include_once( dirname( __FILE__ ) . '/api/procurements.php' );
                include_once( dirname( __FILE__ ) . '/api/products.php' );
                include_once( dirname( __FILE__ ) . '/api/providers.php' );
                include_once( dirname( __FILE__ ) . '/api/registers.php' );
                include_once( dirname( __FILE__ ) . '/api/reset.php' );
                include_once( dirname( __FILE__ ) . '/api/rewards.php' );
                include_once( dirname( __FILE__ ) . '/api/transfer.php' );
                include_once( dirname( __FILE__ ) . '/api/taxes.php' );
                include_once( dirname( __FILE__ ) . '/api/units.php' );
                include_once( dirname( __FILE__ ) . '/api/crud/procurements.php' );
            });
        }
        include_once( dirname( __FILE__ ) . '/api/stores.php' );
    });
});

Route::get( 'api/validation', function() {
    $validation     =   app()->make( Validation::class );
    return $validation->from( UnitsGroupsFields::class )->extract( 'createUnitGroup' );
});