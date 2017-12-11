<?php
namespace Famelo\Oauth\Security\MissingPartyHandler;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Famelo\Oauth\Domain\Model\OAuthToken;
use Famelo\Oauth\Domain\Repository\OAuthTokenRepository;
use Famelo\Oauth\Security\Authentication\Token\OAuth;
use Famelo\Oauth\Services\OauthService;
use OAuth\Common\Token\TokenInterface as OAuthTokenInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Policy\Role;
use Neos\Flow\Security\Policy\RoleRepository;

/**
 */
abstract class AbstractMissingPartyHandler implements MissintPartyHandlerInterface {

	/**
	 * @var string
	 */
	protected $options;

	public function __construct($options) {
		$this->options = $options;
	}

}