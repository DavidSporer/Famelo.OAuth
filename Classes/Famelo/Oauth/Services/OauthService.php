<?php
namespace Famelo\Oauth\Services;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\ServiceFactory;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Routing\UriBuilder;

/**
 * @Flow\Scope("singleton")
 */
class OauthService {
	/**
	 * @Flow\Inject(setting="Services")
	 * @var array
	 */
	protected $services;

	/**
	 * @var ServiceFactory
	 */
	protected $serviceFactory;

	/**
	 * @var TokenStorageInterface
	 */
	protected $storage;

	/**
	 * @Flow\Inject
	 * @var UriBuilder
	 */
	protected $uriBuilder;

	/**
	 * @var ActionRequest
	 */
	protected $request;

	public function injectRequest($request) {
		$this->request = $request;
	}

	public function __construct() {
		$this->serviceFactory = new ServiceFactory();
		$this->storage = new Session();
	}

	public function getService($serviceName, $authorizationRequired = FALSE) {
		$serviceConfiguration = $this->services[$serviceName];

		$this->uriBuilder->setRequest($this->request);
		$this->uriBuilder->setCreateAbsoluteUri(TRUE);

		$credentials = new Credentials(
		    $serviceConfiguration['Key'],
		    $serviceConfiguration['Secret'],
		    $this->uriBuilder->uriFor('requestToken', array('serviceName' => $serviceName))
		);

		$scopes = array();
		if (isset($serviceConfiguration['Scopes'])) {
			$scopes = $serviceConfiguration['Scopes'];
		}

		$service = $this->serviceFactory->createService($serviceName, $credentials, $this->storage, $scopes);

		if ($authorizationRequired === TRUE) {
			if (!$service->getStorage()->hasAccessToken($serviceName)) {
				$uri = $this->uriBuilder->uriFor('requestAuthorization', array('serviceName' => $serviceName), 'Oauth', 'Famelo.Oauth');
				header('Location: ' . $uri);
				exit();
			}

			$token = $service->getStorage()->retrieveAccessToken($serviceName);

	        if ($token->getEndOfLife() !== TokenInterface::EOL_NEVER_EXPIRES
	            && $token->getEndOfLife() !== TokenInterface::EOL_UNKNOWN
	            && time() > $token->getEndOfLife()
	        ) {
	            $service->refreshAccessToken($token);
	        }
        }

        return $service;
	}

	public function getServices() {
		return $this->services;
	}

}