# Famelo.Oauth

This packages helps you setup a login based on common oauth providers like dropbox, github, etc.

# Usage

## 1. Add Oauth Services

```
Famelo:
  Oauth:
    Services:
      Dropbox:
        Key: ...
        Secret: ...
        Logo: resource://Famelo.Oauth/Public/Media/Icons/Dropbox.png

      GitHub:
        Key: ...
        Secret: ...
        Scopes:
          - user
        Logo: resource://Famelo.Oauth/Public/Media/Icons/GitHub.png

      Harvest:
        Key: ...
        Secret: ...
        Logo: resource://Famelo.Oauth/Public/Media/Icons/Harvest.png
```

## 2. Configure MissingPartyHandler

```
Famelo:
  Oauth:
    missingPartyHandler:
        className: '\Famelo\Oauth\Security\MissingPartyHandler\AutoCreatePartyHandler'
        options:
          uri: '/'
```

## 3. configure Authentication

```
TYPO3:
  Flow:
    security:
      enable: true
      authentication:
        providers:
          OAuthProvider:
            provider: 'Famelo\Oauth\Security\Authentication\OAuthAuthenticationProvider'
            tokenClass: 'Famelo\Oauth\Security\Authentication\Token\OAuth'
            entryPoint: WebRedirect
            entryPointOptions:
              uri: login
            providerOptions:
              partyClassName: \My\Package\Domain\Model\User
              roles:
                - My.Package:Usergroup

```

## 4. include Routes.yaml

```
-
  name: 'Oauth'
  uriPattern: '<OauthSubroutes>'
  defaults:
    '@format': 'html'
  subRoutes:
    OauthSubroutes:
      package: Famelo.Oauth
```

## 5. create user model

```
<?php

/**
 * @Flow\Entity
 */
class User extends \TYPO3\Party\Domain\Model\AbstractParty {

    use OAuthParty;

    /**
     * Returns the accounts of this party
     *
     * @param \Doctrine\Common\Collections\Collection $accounts
     */
    public function setAccounts(\Doctrine\Common\Collections\Collection $accounts) {
        $this->accounts = $accounts;
    }

    /**
     * This method receives the service information about a user.
     * Use this to fill your party with available information
     */
    public function fillFromService($source) {
        // $this->setUsername($source->getUsername());
        // $this->setEmail($source->getEmail());
    }

}
```