<?php
// src/Service/PriceFormatter.php
namespace App\Service;

class PriceFormatter
{
    public function formatPrixMap(?int $prixCentimes): string
    {
        if ($prixCentimes === null) {
            return 'Prix non disponible';
        }
    
        $prixStandard = $prixCentimes * 100;
        
        $milliards = floor($prixStandard / 1000000000);
        $reste = $prixStandard % 1000000000;
        $millions = floor($reste / 1000000);
        $milliers = floor(($reste % 1000000) / 1000);
        $unites = $reste % 1000;
    
        $result = '';
        
        if ($milliards > 0) {
            $result .= number_format($milliards, 0, ',', ' ') . ' Md';
        }
        
        if ($millions > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($millions, 0, ',', ' ') . ' M';
        }
        
        if ($milliers > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($milliers, 0, ',', ' ') . ' Mille';
        }
        
        if ($unites > 0 && empty($result)) {
            $result .= number_format($unites, 0, ',', ' ');
        }
    
        if (empty($result)) {
            return '0 DZD';
        }
    
        return $result . ' DZD';
    }
}