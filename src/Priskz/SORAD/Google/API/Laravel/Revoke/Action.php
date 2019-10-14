<?php

namespace Priskz\SORAD\Google\API\Laravel\Revoke;

use URL;
use Priskz\Payload\Payload;
use Priskz\SORAD\Action\LaravelAction;
use Google;

class Action extends LaravelAction
{
	/**
	 * @var  array  Request data configuration.
	 */
	protected $config = [
		'options'              => 'array',
		'options.access_token' => 'required',
		'options.redirect_uri' => 'required',
		'scope'                => ''
	];

	/**
	 *	Action Logic
	 */
	public function execute($data)
	{
		// Add this module's redirect URL to the options.
		$data['options'] = [
			'redirect_uri' => URL::route('google.authenticate')
		];

		// Move the access token to the options.
		if(array_key_exists('access_token', $data))
		{
			$data['options']['access_token'] = $data['access_token'];

			unset($data['access_token']);
		}

		$payload = $this->processor->process($data, $this->config);

		if( ! $payload->isStatus(Payload::STATUS_VALID))
		{
			return $payload;
		}

		return Google::revokeToken($data['options']);
	}
}