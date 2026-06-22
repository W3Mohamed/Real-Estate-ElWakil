<?php
// src/Controller/Admin/PriceFormatController.php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PriceFormatter;

#[Route('/admin')]
class PriceFormatController extends AbstractController
{
    #[Route('/format-price', name: 'admin_format_price')]
    public function formatPrice(Request $request, PriceFormatter $formatter): Response
    {
        $price = $request->query->get('price');
        return new Response($formatter->formatPrixMap($price ? (int)$price : null));
    }
}