<?php
namespace Famelo\Oauth\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use Famelo\Oauth\Services\OauthService;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use TYPO3\Flow\Annotations as Flow;

class OAuthAuthenticationController extends AbstractOAuthAuthenticationController {

	/**
	 * @Flow\Inject
	 * @var OauthService
	 */
	protected $oauthService;

	/**
	 * @Flow\Inject(setting="security.authentication.providers", package="TYPO3.Flow")
	 * @var array
	 */
	protected $authenticationProviders;

	/**
	 * @return void
	 */
	public function loginAction() {
		$this->view->assign('services', $this->oauthService->getServices());
	}

	/**
	 * @param string $serviceName
	 * @return void
	 */
	public function requestAuthorizationAction($serviceName) {
        $serviceName = ucfirst($serviceName);
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
		$this->redirect('authenticate', NULL, NULL, array('serviceName' => $serviceName));
	}

	/**
	 * Redirects to a potentially intercepted request. Returns an error message if there has been none.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	protected function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		if ($originalRequest !== NULL) {
			$this->redirectToRequest($originalRequest);
		}

		$account = $this->securityContext->getAccount();
		if (isset($this->authenticationProviders[$account->getAuthenticationProviderName()]['redirectTarget'])){
			$redirectTarget = $this->authenticationProviders[$account->getAuthenticationProviderName()]['redirectTarget'];
			$action = isset($redirectTarget['action']) ? $redirectTarget['action'] : 'index';
			$arguments = isset($redirectTarget['arguments']) ? $redirectTarget['arguments'] : array();
			$controller = isset($redirectTarget['controller']) ? $redirectTarget['controller'] : NULL;
			$package = isset($redirectTarget['package']) ? $redirectTarget['package'] : NULL;

			$this->redirect($action, $controller, $package, $arguments);
		}
	}

}