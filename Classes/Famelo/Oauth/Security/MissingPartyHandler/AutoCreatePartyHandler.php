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
use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Authentication\Token\UsernamePassword;
use Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException;
use Neos\Flow\Security\Policy\Role;

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
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \Neos\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \Neos\Flow\Security\Policy\PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

	public function handle($token, $service) {
		$partyClassName = $this->options['partyClassName'];
		$party = new $partyClassName();

		$extractorFactory = new \OAuth\UserData\ExtractorFactory();
		$extractor = $extractorFactory->get($service);

		$party->setUserId($extractor->getUniqueId());
		$party->fillFromService($extractor);

		$account = new Account();
		$account->setAccountIdentifier($this->options['providerName'] . ':' . $extractor->getUniqueId());
		$account->setAuthenticationProviderName($this->options['providerName']);

		$party->addAccount($account);

		if (isset($this->options['roles'])) {
			foreach ($this->options['roles'] as $roleName) {
				$account->addRole($this->policyService->getRole($roleName));
			}
		}

		$this->accountRepository->add($account);
		$this->persistenceManager->add($party);

		$this->persistenceManager->persistAll();

		return $party;
	}

}