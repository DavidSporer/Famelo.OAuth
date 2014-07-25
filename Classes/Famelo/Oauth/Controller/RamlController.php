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

class RamlController extends \TYPO3\Flow\Mvc\Controller\ActionController {
	/**
	 * @return void
	 */
	public function indexAction() {
		// $gitHub = new \Famelo\Oauth\Api\ApiService('Harvest');
		// // $invoice = $gitHub->get('/invoices/{id}', array('id' => 5100079));
		// // var_dump($invoice);
		// $invoices = $gitHub->get('/invoices');
		// foreach ($invoices as $invoice) {
		// 	var_dump($invoice);
		// 	var_dump($invoice->details);
		// }

		$gitHub = new \Famelo\Oauth\Api\ApiService('GitHub');
		$user = $gitHub->get('/users/{username}', array('username' => 'mneuhaus'));
		var_dump($user);
	}

}