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
use TYPO3\Flow\Mvc\Controller\ActionController;

class OauthController extends ActionController
{

    /**
     * @Flow\Inject
     * @var OauthService
     */
    protected $oauthService;

    /**
     * @Flow\Inject(setting="Services")
     * @var array
     */
    protected $services;

    /**
     * @return void
     */
    public function indexAction()
    {
    }

    /**
     * @param string $serviceName
     * @return void
     */
    public function requestAuthorizationAction($serviceName)
    {
        $service = $this->oauthService->getService($serviceName);
        $uri = $service->getAuthorizationUri();

        $this->redirectToUri($uri);
    }

    /**
     * @param string $serviceName
     * @return void
     */
    public function requestTokenAction($serviceName)
    {
        $service = $this->oauthService->getService($serviceName);
        $result = $service->requestAccessToken($_GET['code']);
        die(\TYPO3\Flow\var_dump($result));

        $serviceConfiguration = $this->services[$serviceName];
        if (isset($serviceConfiguration['Redirect'])) {
            $redirectTarget = $serviceConfiguration['Redirect'];
            $this->redirect(
                $redirectTarget['action'],
                $redirectTarget['controller'],
                $redirectTarget['package'],
                array(
                    'result' => $result,
                    'serviceName' => $serviceName
                )
            );
        } else {
            $this->redirect('index');
        }
    }

}