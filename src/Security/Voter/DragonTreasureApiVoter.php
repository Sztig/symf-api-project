<?php

namespace App\Security\Voter;

use App\ApiResource\DragonTreasureApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DragonTreasureApiVoter extends Voter
{
    public const EDIT = 'EDIT';

    public function __construct(
        private Security $security
    ) {

    }
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT
            && $subject instanceof DragonTreasureApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        assert($subject instanceof DragonTreasureApi);

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
        case self::EDIT:
            if(!$this->security->isGranted('ROLE_TREASURE_EDIT')) {
                return false;
            }

            if($subject->owner?->id === $user->getId()) {
                return true;
            }
            break;
        }

        return false;
    }
}
