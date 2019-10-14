<?php

namespace Priskz\SORAD\Google\API\Laravel\Authenticate;

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
		'options.redirect_uri' => 'required',
		'code'                 => 'required',
		'error'                => ''
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

		$payload = $this->processor->process($data, $this->config);

		if( ! $payload->isStatus(Payload::STATUS_VALID))
		{
			return $payload;
		}

		return Google::authenticate($data);
	}
}