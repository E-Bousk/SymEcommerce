<?php

namespace App\Controller\Purchase;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasesListController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER", message="Vous devez être connecté avant de voir vos commandes")
     * @Route("/purchases", name="purchase_index")
     */
    public function index()
    {
        $user= $this->getUser();

        return $this->render('purchase/index.html.twig', [
            'purchases' => $user->getPurchases()
        ]);
    }
}
