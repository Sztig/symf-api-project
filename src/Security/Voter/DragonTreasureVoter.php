<?php

namespace App\Security\Voter;

use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DragonTreasureVoter extends Voter
{
    public const EDIT = 'EDIT';

    public function __construct(
        private Security $security
    )
    {

    }
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT
            && $subject instanceof DragonTreasure;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        assert($subject instanceof DragonTreasure);

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
                if(!$this->security->isGranted('ROLE_TREASURE_EDIT')){
                    return false;
                }

                if($subject->getOwner() === $user){
                    return true;
                }
                break;
        }

        return false;
    }
}
