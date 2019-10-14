<?php

namespace Priskz\SORAD\Google\API\Laravel\Authenticate;

use Priskz\SORAD\Responder\LaravelResponder;

class Responder extends LaravelResponder
{
	/**
	 *	Constructor
	 */
	public function __construct(Action $action)
	{
		$this->action = $action;
	}
}