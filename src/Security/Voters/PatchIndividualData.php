<?php

namespace App\Services\Voters;

use App\Entity\IndividualData;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class PatchIndividualData implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if(!$subject instanceof IndividualData){
            return self::ACCESS_ABSTAIN;
        }

        if(!in_array('INDIVIDUAL_DATA_PATCH', $attributes)){
            return self::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();
        
        if(!$user instanceof UserInterface){
            return self::ACCESS_DENIED;
        }

        if($subject->getIndividual()->getUser() !== $user){
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }
}