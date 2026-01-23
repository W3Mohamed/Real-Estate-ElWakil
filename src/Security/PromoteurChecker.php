<?php

namespace App\Security;

use App\Entity\Promoteur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PromoteurChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Promoteur) {
            return;
        }
        // Ici on ne fait rien avant l'auth
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Promoteur) {
            return;
        }

        // Calcul de la date de fin d'abonnement
        $subscribedAt = $user->getSubscribedAt();
        $duration = $user->getDuration();

        if ($subscribedAt && $duration) {
            $expirationDate = $subscribedAt->add(new \DateInterval("P{$duration}M"));
            $now = new \DateTimeImmutable();

            if ($now > $expirationDate) {
                throw new CustomUserMessageAuthenticationException(
                    'Votre abonnement a expiré. Veuillez contacter l\'administrateur pour renouveler.'
                );
            }
        }
    }
}