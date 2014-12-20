<?php
namespace Famelo\Oauth\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use Famelo\Oauth\Services\OauthService;
use Famelo\Soul\Controller\AbstractSoulController;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use TYPO3\Flow\Annotations as Flow;

class OauthController extends AbstractSoulController {

	/**
	 * @Flow\Inject
	 * @var OauthService
	 */
	protected $oauthService;

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * @param string $serviceName
	 * @return void
	 */
	public function requestAuthorizationAction($serviceName) {
		$service = $this->oauthService->getService($serviceName);
		$uri = $service->getAuthorizationUri();

		$this->redirectToUri($uri);
	}

	/**
	 * @param string $serviceName
	 * @return void
	 */
	public function requestTokenAction($serviceName) {
		$service = $this->oauthService->getService($serviceName);
		$result = $service->requestAccessToken($_GET['code']);
		$this->redirect('index');
	}

}