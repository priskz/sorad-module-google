<?php namespace Priskz\SORAD\Google\Service;

use Priskz\Payload\Payload;
use Priskz\SORAD\Service\Laravel\GenericService;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Oauth2;

class Service extends GenericService
{
    /**
     * @property string $oauth - Fully qualified path to the web app's OAuth 2.0 credentials.
     */
	protected $oauth;

    /**
     * @property array $client google\apiclient\src\Google\Client
     */
	protected $client;

    /**
     * @property array $service
     */
	protected $service = [
		'gmail' => [
			'class' => 'Google_Service_Gmail',
			'scope' => [
				'default' => Google_Service_Gmail::MAIL_GOOGLE_COM
			]
		],
		'oauth2' => [
			'class' => 'Google_Service_Oauth2',
			'scope' => [
				'default' => Google_Service_Oauth2::USERINFO_EMAIL
			]
		]
	];

	/**
	 *	Constructor
	 */
	public function __construct(string $alias, string $oauth)
	{
		parent::__construct($alias);

		$this->setOauth($oauth);
		$this->setClient();
	}

	/**
	 * Get a Google access token's information.
     *
     * @param  string|array $accessToken - Authorized JSON token.
     * @param  array        $options
     * @return Payload
	 */
	public function getTokenInfo($accessToken, $options = [])
	{
		// Set all of the dynamic client options.
		$this->setClientOptions($options);

		// Set the access token dependant upon var type given.
		switch(gettype($accessToken))
		{
			case 'array':
				// Set option parameter value.
				$optionParameters['access_token'] = $accessToken['access_token'];
			break;

			case 'string':
				// JSON decode the stored authorization secret
				$decodedSecret = json_decode($accessToken, true);

				// Set option parameter value.
				$optionParameters['access_token'] = $decodedSecret['access_token'];
			break;

			default:

			break;
		}

		// Init
		$service = new $this->service['oauth2']['class']($this->client);

		return $service->tokeninfo($optionParameters);
	}

	/**
	 * Get Message with given ID.
     *
     * @param  array $data - Expected Keys: access_token
     * @return Payload
	 */
	public function getUserInfo(array $data)
	{
		// Set all of the dynamic client options.
		$this->setClientOptions($data);

		// Init
		$service = new $this->service['oauth2']['class']($this->client);

		return $service->userinfo_v2_me->get();
	}

	/**
	 * Get all the Labels in the user's mailbox.
	 *
     * @param  string $accessToken - Authorized JSON token.
     * @param  string $options
     * @return Payload
	 */
	public function listLabels($accessToken, $options = [])
	{
		// Set access_token value.
		$options['access_token'] = $accessToken;

		// Set all of the dynamic client options.
		$this->setClientOptions($options);

		// Init
		$service = new $this->service['gmail']['class']($this->client);

		return $service->users_labels->listUsersLabels('me');
	}

	/**
	 * List Users Gmail Labels
	 *
     * @param  string $accessToken - Authorized JSON token.
     * @param  string $messageId   - The id of the message to get.
     * @return Payload
	 */
	public function listMessages($accessToken, $options = [])
	{
		// Set access_token value.
		$options['access_token'] = $accessToken;

		// Set all of the dynamic client options.
		$this->setClientOptions($options);

		// Init
		$service = new $this->service['gmail']['class']($this->client);

		return $service->users_messages->listUsersMessages('me', $options);
	}

	/**
	 * Get Message with given ID.
	 *
     * @param  string $accessToken - Authorized JSON token.
     * @param  string $messageId - The id of the message to get.
     * @return Payload
	 */
	public function getMessage($accessToken, $messageId, $options = [])
	{
		// Set access_token value.
		$options['access_token'] = $accessToken;

		// Set all of the dynamic client options.
		$this->setClientOptions($options);

		// Init
		$service = new $this->service['gmail']['class']($this->client);

		return $service->users_messages->get('me', $messageId);
	}

	/**
     * Authorize.
     *
     * @param  array $data - Expected Keys: N/A
     * @return Payload
	 */
	public function authorize(array $data)
	{
		// @todo: Refactor options to a configuration.

		// Additional options to set.
		$data['options']['prompt']                 = 'force';
		$data['options']['acccess_type']           = 'offline';
		$data['options']['include_granted_scopes'] = true;

		// Set all of the dynamic client options.
		$this->setClientOptions($data['options']);

		// Set specified scope if provided, otherwise default to all configured defaults.
		if(array_key_exists('scope', $data))
		{
			foreach($data['scope'] as $name => $scope)
			{
				$this->client->addScope($this->service[$name]['scope'][$scope]);
			}
		}
		else
		{
			foreach($this->service as $name => $config)
			{
				$this->client->addScope($this->service[$name]['scope']['default']);
			}
		}

		// Return the authorization URL.
		return new Payload($this->client->createAuthUrl(), 'valid');
	}

	/**
	 * Authenticate an authorization code.
     *
     * @param  array $data - Expected Keys: code, options
     * @return Payload
	 */
	public function authenticate(array $data)
	{
		// Set all of the dynamic client options.
		$this->setClientOptions($data['options']);

		// Exchange code for an access token.
		$accessToken = $this->client->authenticate($data['code']);

		if(array_key_exists('error', $accessToken))
		{
			return new Payload($accessToken, 'not_authenticated');
		}

		return new Payload($accessToken, 'authenticated');
	}

	/**
     * Revoke access token authentication.
     *
     * @param  array $options - Expected Keys: options
     * @return Payload
	 */
	public function revokeToken(array $options)
	{
		// Set all of the dynamic client options.
		$this->setClientOptions($options);
		
		// Utilize the client to revoke token.
		$successful = $this->client->revokeToken();

		if($successful)
		{
			return new Payload(null, 'revoked');
		}

		return new Payload(null, 'not_revoked');
	}

    /**
     * Set $secret property - if null given check for env value.
     * 
     * @return void
     */
    protected function setClient()
    {
    	// Init new client.
		$this->client = new Google_Client();

		// Set the authorization w/ Web Applicaiton client_secret provided by Google.
		$this->client->setAuthConfig($this->oauth);
    }

    /**
     * Set options on the client prior to utilizing it.
     *
     * @param  array $options
     * @return void
     */
    protected function setClientOptions(array $options)
    {
    	foreach($options as $key => $value)
    	{
			switch($key)
			{
				case 'redirect_uri':
					// Set the callback URL (matches Authorized redirect URI in Google API Credentials Dashboard).
					$this->client->setRedirectUri($value);
				break;

				case 'access_token':
					$this->client->setAccessToken($value);
				break;

				case 'acccess_type':
					// Enables access while not logged in.
					$this->client->setAccessType($value);
				break;

				case 'include_granted_scopes':
					// Enables incremental authorization.
					$this->client->setIncludeGrantedScopes($value);
				break;

				case 'prompt':
					$this->client->setApprovalPrompt($value);
				break;

				default:
					// N/A
				break;
			}
    	}
    }

    /**
     * Set $oauth property - if null given check for env value.
     *
     * @param  string $oauth - Fully qualified path to file.
     * @return void
     */
    protected function setOauth($oauth)
    {
        $this->oauth = $oauth;
    }
}