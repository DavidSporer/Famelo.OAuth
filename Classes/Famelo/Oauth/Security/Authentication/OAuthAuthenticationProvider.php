<?php
namespace Famelo\Oauth\Security\Authentication;

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
class OAuthAuthenticationProvider extends AbstractProvider {

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
	 * @Flow\Inject
	 * @var OauthService
	 */
	protected $oauthService;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * Returns the class names of the tokens this provider can authenticate.
	 *
	 * @return array
	 */
	public function getTokenClassNames() {
		return array('Famelo\Oauth\Security\Authentication\Token\OAuth');
	}

	/**
	 * Checks the given token for validity and sets the token authentication status
	 * accordingly (success, wrong credentials or no credentials given).
	 *
	 * @param \TYPO3\Flow\Security\Authentication\TokenInterface $authenticationToken The token to be authenticated
	 * @return void
	 * @throws \TYPO3\Flow\Security\Exception\UnsupportedAuthenticationTokenException
	 */
	public function authenticate(TokenInterface $authenticationToken) {
		if (!($authenticationToken instanceof OAuth)) {
			throw new UnsupportedAuthenticationTokenException('This provider cannot authenticate the given token.', 1217339840);
		}

		/** @var $account \TYPO3\Flow\Security\Account */
		$account = NULL;
		$credentials = $authenticationToken->getCredentials();

		if (is_array($credentials) && isset($credentials['serviceName'])) {
			$serviceName = $credentials['serviceName'];
			$service = $this->oauthService->getService($serviceName);

			if (!$service->getStorage()->hasAccessToken($serviceName)) {
				$authenticationToken->setAuthenticationStatus(TokenInterface::NO_CREDENTIALS_GIVEN);
			}

			$token = $service->getStorage()->retrieveAccessToken($serviceName);

			if ($this->isOAuthTokenValid($token) === FALSE) {
				$token = $service->refreshAccessToken($token);
			}

			$partyClassName = $this->options['partyClassName'];

			$query = $this->persistenceManager->createQueryForType($partyClassName);
			$query->matching($query->equals('accessToken', $token->getAccessToken()));
			$party = $query->execute()->getFirst();

			if ($party === NULL) {
				$party = $this->createParty($token, $service);
			}

			$authenticationToken->setAccount($party->getAccounts()->current());

			if ($this->isOAuthTokenValid($token)) {
	            $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
	        } else {
	        	$authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
	        }
		}
	}

	public function createParty($token, $service) {
		$partyClassName = $this->options['partyClassName'];

		$extractorFactory = new \OAuth\UserData\ExtractorFactory();
		$extractor = $extractorFactory->get($service);

		$query = $this->persistenceManager->createQueryForType($partyClassName);
		$query->matching($query->equals('userId', $extractor->getUniqueId()));
		$party = $query->execute()->getFirst();

		if ($party !== NULL) {
			$party->setAccessToken($token->getAccessToken());
			$this->persistenceManager->update($party);
		} else {
			$party = new $partyClassName();

			$party->setAccessToken($token->getAccessToken());
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
		}

		$this->persistenceManager->persistAll();

		return $party;
	}

	public function isOAuthTokenValid($token) {
		return $token->getEndOfLife() === OAuthTokenInterface::EOL_NEVER_EXPIRES
				|| $token->getEndOfLife() === OAuthTokenInterface::EOL_UNKNOWN
				|| time() < $token->getEndOfLife();
	}

}
