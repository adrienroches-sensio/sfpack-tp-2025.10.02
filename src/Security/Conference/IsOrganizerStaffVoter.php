<?php

declare(strict_types=1);

namespace App\Security\Conference;

use App\Security\ConferencePermissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class IsOrganizerStaffVoter implements VoterInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function vote(TokenInterface $token, mixed $subject, array $attributes, ?Vote $vote = null): int
    {
        [$attribute] = $attributes;

        if ( $attribute !== ConferencePermissions::NEW) {
            return self::ACCESS_ABSTAIN;
        }

        $isRoleOrganizer = $this->authorizationChecker->isGranted('ROLE_ORGANIZER');

        if (false === $isRoleOrganizer) {
            $vote?->addReason('User is not an organizer.');

            return self::ACCESS_ABSTAIN;
        }

        return self::ACCESS_GRANTED;
    }
}
