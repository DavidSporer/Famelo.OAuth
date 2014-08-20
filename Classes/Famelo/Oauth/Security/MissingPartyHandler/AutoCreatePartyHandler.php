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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Security\Account;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Flow\Security\Authentication\Provider\AbstractProvider;
use TYPO3\Flow\Security\Authentication\TokenInterface;
use TYPO3\Flow\Security\Authentication\Token\UsernamePassword;
use TYPO3\Flow\Security\Exception\UnsupportedAuthenticationTokenException;
use TYPO3\Flow\Security\Policy\Role;
use TYPO3\Flow\Security\Policy\RoleRepository;

/**
 */
class AutoCreatePartyHandler extends AbstractMissingPartyHandler {

	/**
	 * @Flow\Inject
	 * @var AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var RoleRepository
	 */
	protected $roleRepository;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	public function handle($token, $service) {
		$party = new $partyClassName();

		$party->setUserId($extractor->getUniqueId());
		$party->fillFromService($extractor);

		$account = new Account();
		$account->setAccountIdentifier($this->name . ':' . $extractor->getUniqueId());
		$account->setAuthenticationProviderName($this->name);

		$party->addAccount($account);

		if (isset($this->options['roles'])) {
			foreach ($this->options['roles'] as $roleName) {
				$account->addRole($this->roleRepository->findByIdentifier($roleName));
			}
		}

		$this->accountRepository->add($account);
		$this->persistenceManager->add($party);

		$this->persistenceManager->persistAll();

		return $party;
	}

}