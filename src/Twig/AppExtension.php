<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('currentSortDirection', [$this, 'currentSortDirection']),
        ];
    }

    public function currentSortDirection(string $field, array $currentSort): string
    {
        // Si le tableau est vide ou si le champ n'existe pas
        if (empty($currentSort) || !isset($currentSort[$field])) {
            return 'DESC'; // Premier clic = DESC
        }
        return $currentSort[$field] === 'ASC' ? 'DESC' : 'ASC';
    }
}