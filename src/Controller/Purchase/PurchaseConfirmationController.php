<?php

namespace App\Controller\Purchase;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchaseConfirmationController extends AbstractController
{
    protected $cartService;
    protected $em;
    // protected $persister;

    public function __construct(CartService $cartService, EntityManagerInterface $em)
    {
        $this->cartService = $cartService;
        $this->em = $em;
        // $this->persister = $persister;
    }
    
    /**
     * @Route("/purchase/confirm", name="purchase_confirm")
     * @IsGranted("ROLE_USER", message="Vous devez être connecté pour confirmer une commande")
     */
    public function confirm(Request $request)
    {

        // 1. Nous voulons lire les données du formulaire (FormFactoryInterface / Request)
        $form = $this->createForm(CartConfirmationType::class);

        $form->handleRequest($request);

        // 2. Si le formulaire n'a pas été soumis : dégager
        if (!$form->isSubmitted()) {
            $this->addFlash('warning', 'vous devez remplir le formulaire de confirmation');

            return $this->redirectToRoute('cart_show');
        }

        // 3. Si je ne suis pas connecté : dégager (Security)
        // (--> avec @IsGranted)
        $user= $this->getUser();

        // 4. Si il n'y a pas de produits dans mon panier : dégager (CartService)
        $cartItems = $this->cartService->getDetailedCartItems();

        if (count($cartItems) === 0) {
            $this->addFlash('warning', 'Vous ne pouvez confirmer une commande avec un panier vide');

            return $this->redirectToRoute('cart_show');
        }

        // 5. Nous allons créer une Purchase
        // Voir vidéo 17.11 à 3:00
        /** @var Purchase */
        $purchase= $form->getData();
        
        // 6. Nous allons la lier avec l'utilisateur actuellement connecté (Security)
        $purchase->setUser($user)
                ->setPurchasedAt(new DateTime())
                ->setTotal($this->cartService->getTotal())
        ;
        $this->em->persist($purchase);
        
        // 7. Nous allons la lier avec les produits qui sont dans le panier (CartService)
        foreach($this->cartService->getDetailedCartItems() as $cartItem) {
            $purchaseItem= new PurchaseItem;
            $purchaseItem->setPurchase($purchase)
            ->setProduct($cartItem->product)
            ->setProductName($cartItem->product->getName())
            ->setQuantity($cartItem->quantity)
            ->setTotal($cartItem->getTotalItem())
            ->setProductPrice($cartItem->product->getPrice())
            ;

            $this->em->persist($purchaseItem);
            dump($purchaseItem);
        }
        // dd($purchase);

        // 8. Nous allons enregister la commande (EntityManagerInterface)
        $this->em->flush();

        $this->cartService->empty();

        $this->addFlash('success', 'La commande à bien été validée');
        return $this->redirectToRoute('purchase_index');
    }
}