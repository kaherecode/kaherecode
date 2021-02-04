<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Tutorial;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TutorialVoter extends Voter
{
    /**
     * @var Security
     */
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    const EDIT = 'edit';

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Tutorial) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(
        string $attribute,
        $subject,
        TokenInterface $token
    ) {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $user === $subject->getAuthor();
    }
}
