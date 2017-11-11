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
class OauthService
{
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

    public function injectRequest($request)
    {
        $this->request = $request;
    }

    public function __construct()
    {
        $this->serviceFactory = new ServiceFactory();
        $this->storage = new Session();
    }

    public function getService($serviceName, $authorizationRequired = FALSE)
    {
        $serviceName = $this->findServiceName($serviceName);

        $serviceConfiguration = $this->services[$serviceName];

        $this->uriBuilder->setRequest($this->request);
        $this->uriBuilder->setCreateAbsoluteUri(TRUE);

        $redirectUri = null;
        if (isset($serviceConfiguration['Redirect'])) {
            $redirectTarget = $serviceConfiguration['Redirect'];

            $redirectUri = $this->uriBuilder->uriFor(
                $redirectTarget['action'],
                array(
                    'serviceName' => $serviceName
                ),
                $redirectTarget['controller'],
                $redirectTarget['package']);
        } else {
            $redirectUri = $this->uriBuilder->uriFor('requestToken', array('serviceName' => $serviceName));
        }

        if(!strpos($redirectUri, 'http')) {
            $redirectUri = 'https://app.passcreator.com' . $redirectUri;
        }

        $credentials = new Credentials(
            $serviceConfiguration['Key'],
            $serviceConfiguration['Secret'],
            $redirectUri
        );

        $scopes = array();
        if (isset($serviceConfiguration['Scopes'])) {
            $scopes = $serviceConfiguration['Scopes'];
        }

        $service = $this->serviceFactory->createService($serviceName, $credentials, $this->storage, $scopes);

        if ($authorizationRequired === TRUE) {
            if (!$service->getStorage()->hasAccessToken($serviceName)) {
                $uri = $this->uriBuilder->uriFor('requestAuthorization', array('serviceName' => $serviceName), 'Oauth', 'Famelo.Oauth');

                if(!strpos($uri, 'http')) {
                    $uri = 'https://app.passcreator.com' . $uri;
                }

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

    public function getServices()
    {
        return $this->services;
    }

    public function findServiceName($lowercaseServiceName)
    {
        foreach ($this->services as $serviceName => $serviceConfiguration) {
            if (strtolower($lowercaseServiceName) == strtolower($serviceName)) {
                return $serviceName;
            }
        }
        return $lowercaseServiceName;
    }

    public function getServiceToken($serviceName)
    {
        $service = $this->getService($serviceName);
        $token = $service->getStorage()->retrieveAccessToken($serviceName);
        return $token;
    }

}
