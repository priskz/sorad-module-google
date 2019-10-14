<?php namespace Priskz\SORAD\Google\API\Laravel;

use Illuminate\Support\Facades\Route;
use Priskz\SORAD\Routes\Laravel\AbstractRoutes;

class Routes extends AbstractRoutes
{
    /**
     * @property string $prefix
     */
	protected static $prefix = 'google';

    /**
     * @property array $middleware
     */
	protected static $middleware = ['web'];

    /**
     * @property array $nameSpace
     */
	protected static $namespace = __NAMESPACE__;

    /**
     * Register the route group.
     *
     * @return void
     */
	protected static function register()
	{
		Route::group(['prefix' => static::$prefix, 'middleware' => static::$middleware, 'namespace' => static::$namespace], function()
		{
			// Authorize Google via OAuth2.
			Route::get('authorize/oauth2', 'Authorize\Responder')->name('google.authorize');

			// Authenticate Google access token via OAuth2.
			Route::get('authenticate/oauth2', 'Authenticate\Responder')->name('google.authenticate');

			// Revoke Google access token via OAuth2.
			Route::get('revoke/oauth2', 'Revoke\Responder')->name('google.revoke');
		});
	}
}