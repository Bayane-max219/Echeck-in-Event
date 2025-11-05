<?php

namespace App\Security;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($event, $user),
            self::EDIT => $this->canEdit($event, $user),
            self::DELETE => $this->canDelete($event, $user),
            default => false,
        };
    }

    private function canView(Event $event, User $user): bool
    {
        return $event->getOrganizer() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canEdit(Event $event, User $user): bool
    {
        return $event->getOrganizer() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canDelete(Event $event, User $user): bool
    {
        return $event->getOrganizer() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }
}