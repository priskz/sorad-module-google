<?php namespace Priskz\SORAD\Google\API\Laravel;

use Input, Route, Redirect, URL;
use Priskz\SORAD\Google\API\Laravel\Authorize\Action;
use Priskz\SORAD\Responder\Laravel\AbstractGenericResponder as Responder;

class Authorize extends Responder
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
		// Display messaged based on payload status.
		switch($payload->getStatus())
		{
			case 'valid':
				return Redirect::to($payload->getData());
			break;
		}

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

		return $requestData;
	}
}