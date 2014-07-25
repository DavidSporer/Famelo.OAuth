<?php
namespace Famelo\Oauth\Domain\Traits;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Party\Domain\Model\PersonName;

trait OAuthParty {

    /**
     * @var string
     *
     */
    protected $accessToken;

    /**
     * @var string
     *
     */
    protected $userId;

    public function setExtractor($source) {}

    /**
     * Gets accessToken.
     *
     * @return string $accessToken
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Sets the accessToken.
     *
     * @param string $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * Gets userId.
     *
     * @return string $userId
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Sets the userId.
     *
     * @param string $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

}
