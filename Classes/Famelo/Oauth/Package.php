<?php
namespace Famelo\Oauth;

use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Annotations as Flow;

/**
 * Package base class of the Famelo.Oauth package.
 *
 * @Flow\Scope("singleton")
 */
class Package extends BasePackage {
	public function boot(\Neos\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect(
			'Neos\Flow\Mvc\ActionRequest', 'requestDispatched',
			'Famelo\Oauth\Services\OauthService', 'injectRequest'
		);
	}
}

?>