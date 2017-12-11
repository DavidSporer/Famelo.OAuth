<?php
namespace Famelo\Oauth\Security\Authentication\Token;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\Flow\Annotations as Flow;

/**
 * An authentication token used for simple username and password authentication.
 */
class OAuth extends \Neos\Flow\Security\Authentication\Token\AbstractToken {

	/**
	 * @var array
	 * @Flow\Transient
	 */
	protected $credentials = array(
		'serviceName' => ''
	);

	/**
	 * Updates the username and password credentials from the POST vars, if the POST parameters
	 * are available. Sets the authentication status to REAUTHENTICATION_NEEDED, if credentials have been sent.
	 *
	 * Note: You need to send the username and password in these two POST parameters:
	 *       __authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][username]
	 *   and __authentication[Neos][Flow][Security][Authentication][Token][UsernamePassword][password]
	 *
	 * @param \Neos\Flow\Mvc\ActionRequest $actionRequest The current action request
	 * @return void
	 */
	public function updateCredentials(\Neos\Flow\Mvc\ActionRequest $actionRequest) {
		if ($actionRequest->hasArgument('serviceName')) {
			$this->credentials['serviceName'] = $actionRequest->getArgument('serviceName');
			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		}
	}

	/**
	 * Returns a string representation of the token for logging purposes.
	 *
	 * @return string The username credential
	 */
	public function  __toString() {
		return 'Service: "' . $this->credentials['serviceName'] . '"';
	}

}