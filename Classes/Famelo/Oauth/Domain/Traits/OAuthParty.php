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
    protected $userId;

    public function setExtractor($source) {}

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
