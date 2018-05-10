<?php namespace Priskz\SORAD\Google\API\Laravel\Revoke;

use Priskz\Payload\Payload;
use Priskz\SORAD\Action\Laravel\AbstractAction;
use Google;

class Action extends AbstractAction
{
	/**
	 * @var array Data Keys
	 */
	protected $dataKeys = [
		'options', 'scope'
	];

	/**
	 * @var array Rules
	 */
	protected $rules = [
		'options'              => 'array',
		'options.access_token' => 'required',
		'options.redirect_uri' => 'required',
		'scope'                => ''
	];

	/**
	 *	Main Method
	 */
	public function __invoke($requestData)
	{
		// Process Action Data Keys
		$actionDataPayload = $this->processor->process($requestData, $this->getDataKeys(), $this->getRules());

		if( ! $actionDataPayload->isStatus(Payload::STATUS_VALID))
		{
			return $actionDataPayload;
		}

		// Execute
		return $this->execute($actionDataPayload->getData());
	}

	/**
	 *	Execute
	 */
	public function execute($data)
	{	
		return Google::revokeToken($data['options']);
	}
}