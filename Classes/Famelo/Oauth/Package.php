<?php
namespace Famelo\Oauth;

use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Annotations as Flow;

/**
 * Package base class of the Famelo.Oauth package.
 *
 * @Flow\Scope("singleton")
 */
class Package extends BasePackage {
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect(
			'TYPO3\Flow\Mvc\ActionRequest', 'requestDispatched',
			'Famelo\Oauth\Services\OauthService', 'injectRequest'
		);
	}
}

?>