<?php
namespace Famelo\Oauth\Api;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use Famelo\Oauth\Services\OauthService;
use Famelo\Oauth\Services\TransformationService;
use Guzzle\Http\Client;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use OAuth\Common\Token\TokenInterface;
use OAuth\ServiceFactory;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngine;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 */
class ApiService {
	/**
	 * @var string
	 */
	protected $baseUri;

	/**
	 * @var \OAuth\Common\Service\AbstractService
	 */
	protected $service;

	/**
	 * @var string
	 */
	protected $serviceName;

	/**
	 * @var array
	 */
	protected $blueprint;

	/**
	 * @Flow\Inject(setting="UserAgent")
	 * @var string
	 */
	protected $userAgent;

	/**
	 * @Flow\Inject
	 * @var Client
	 */
	protected $client;

	/**
	 * @Flow\Inject
	 * @var TransformationService
	 */
	protected $transformationService;

	public function __construct($serviceName, OauthService $oauthService) {
		$this->service = $oauthService->getService($serviceName, TRUE);
		$parser = new \Raml\Parser();
		$this->blueprint = $parser->parse('resource://Famelo.Oauth/Private/ApiDefinitions/' . $serviceName . '.raml');
		$this->serviceName = $serviceName;
	}

	public function get($path, $arguments = array()) {
		$blueprint = $this->getBlueprint($path, 'get');
		$path = $this->replacePathArguments($path, $arguments);

		$this->client->setDefaultOption('verify', false);
		$request = $this->client->createRequest('GET', $this->blueprint['baseUri'] . $path);
		$this->prepareRequest($request);
		$response = $this->client->send($request);

		$body = $response->getBody();;

		return $this->transformResponse($response, $blueprint['responses'][$response->getStatusCode()]);
	}

	public function transformResponse($response, $responseProperties) {
		$contentType = $this->blueprint['contentType'];

		if ($responseProperties !== NULL) {
			foreach ($responseProperties as $responseProperty => $configuration) {
				if (!isset($configuration[$contentType]['transformation'])) {
					continue;
				}
				$transformationConfiguration = $configuration[$contentType]['transformation'];
				switch ($responseProperty) {
					case 'body':
							$body = $this->transformContent($response->getBody(), $contentType);
							return $this->transformationService->transform($body, $transformationConfiguration);
						break;
				}
			}
		}

		return $this->transformContent($response->getBody(), $contentType);
	}

	public function transformContent($content, $contentType) {
		switch ($contentType) {
			case 'application/json':
				return json_decode($content);
				break;
		}
	}

	public function replacePathArguments($path, $arguments) {
		foreach ($arguments as $argument => $value) {
			$path = str_replace('{' . $argument . '}', $value, $path);
		}
		return $path;
	}

	public function getBlueprint($path, $method) {
		$paths = explode('/', ltrim($path, '/'));
		$blueprint = $this->blueprint;
		foreach ($paths as $path) {
			$blueprint = $blueprint['/' .  $path];
		}
		return $blueprint[$method];
	}

	public function prepareRequest($request) {
		$token = $this->service->getStorage()->retrieveAccessToken($this->serviceName);

        if ($token->getEndOfLife() !== TokenInterface::EOL_NEVER_EXPIRES
            && $token->getEndOfLife() !== TokenInterface::EOL_UNKNOWN
            && time() > $token->getEndOfLife()
        ) {
            throw new ExpiredTokenException(
                sprintf(
                    'Token expired on %s at %s',
                    date('m/d/Y', $token->getEndOfLife()),
                    date('h:i:s A', $token->getEndOfLife())
                )
            );
        }

        if (isset($this->blueprint['headers'])) {
        	foreach ($this->blueprint['headers'] as $header => $value) {
        		$request->addHeader($header, $value);
        	}
        }

        if (isset($this->blueprint['authorizationMethod'])) {
			switch ($this->blueprint['authorizationMethod']) {
				case 'HEADER_OAUTH':
					$request->addHeader('Authorization', 'OAuth ' . $token->getAccessToken());
					break;
				case 'QUERY_STRING':
					$request->getQuery()->set('access_token', $token->getAccessToken());
					break;
				case 'QUERY_STRING_V2':
					$request->getQuery()->set('oauth2_access_token', $token->getAccessToken());
					break;
				case 'QUERY_STRING_V3':
					$request->getQuery()->set('apikey', $token->getAccessToken());
					break;
				case 'HEADER_BEARER':
					$request->addHeader('Authorization', 'Bearer ' . $token->getAccessToken());
					break;
			}
		}
	}

}