<?php namespace Priskz\SORAD\Google\API\Laravel;

use Input, Route, URL;
use Priskz\SORAD\Google\API\Laravel\Revoke\Action;
use Priskz\SORAD\Responder\Laravel\AbstractGenericResponder as Responder;

class Revoke extends Responder
{
	/**
	 *	Constructor
	 */
	public function __construct(Action $action)
	{
		$this->action = $action;
	}

	/**
	 *	Generate Response
	 */
	public function generateResponse($payload)
	{
		// Return JSON
		return response()->json([
			'data'   => $payload->getData(),
			'status' => $payload->getStatus()
		]);
	}

	/**
	 *	Get Request Data
	 */
	public function getRequestData()
	{
		$requestData = Input::all();

		$requestParamData = Route::getCurrentRoute()->parametersWithoutNulls();

		if ($requestParamData)
		{
			$requestData = array_merge($requestData, $requestParamData);
		}

		// Add this module's redirect URL to the options.
		$requestData['options'] = [
			'redirect_uri' => URL::route('google.authenticate')
		];

		// Move the access token to the options.
		if(array_key_exists('access_token', $requestData))
		{
			$requestData['options']['access_token'] = $requestData['access_token'];

			unset($requestData['access_token']);
		}

		return $requestData;
	}
}