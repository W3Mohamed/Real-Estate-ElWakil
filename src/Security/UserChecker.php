<?php
// src/Security/UserChecker.php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérification du statut
        if (!$user->isStatus()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est désactivé.');
        }

        // Vérification de l'abonnement
        $subscribedAt = $user->getSubscribedAt();
        if ($subscribedAt === null) {
            throw new CustomUserMessageAccountStatusException('Vous n\'avez pas d\'abonnement actif.');
        }

        $now = new \DateTimeImmutable();
        $duree = $user->getDuration() ?? 1; 
        $typeDuration = $user->getTypeDuration() ?? 'mois';

        if ($typeDuration === 'mois') {
            $subscriptionEnd = $subscribedAt->add(new \DateInterval('P'.$duree.'M'));
        } else {
            $subscriptionEnd = $subscribedAt->add(new \DateInterval('P'.$duree.'D'));
        }

        if ($now > $subscriptionEnd) {
            throw new CustomUserMessageAccountStatusException('Votre abonnement a expiré.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Ici vous pourriez ajouter des vérifications après l'authentification
    }
}