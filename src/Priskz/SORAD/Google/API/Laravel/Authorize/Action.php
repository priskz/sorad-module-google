<?php namespace Priskz\SORAD\Google\API\Laravel\Authorize;

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
		'scope'                => 'array',
		'options'              => 'array',
		'options.redirect_uri' => 'required'
	];

	/**
	 *	Main Method
	 */
	public function __invoke($requestData)
	{
		// Process Action Data Keys
		$actionDataPayload = $this->processor->process($requestData, $this->getDataKeys(), $this->getRules());

		if($actionDataPayload->getStatus() != 'valid')
		{
			return $actionDataPayload;
		}

		// Execute.
		return $this->execute($actionDataPayload->getData());
	}

	/**
	 *	Execute
	 */
	public function execute($data)
	{
		return Google::authorize($data);
	}
}