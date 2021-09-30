<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Form\CartConfirmationType;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{

    /**
     * @var productRepository
     */
    protected $productRepository;

    /**
     * @var cartService
     */
    protected $cartService;

    public function __construct(CartService $cartService, ProductRepository $productRepository)
    {
        $this->productRepository= $productRepository;
        $this->cartService= $cartService;
    }

    /**
     * @Route("/cart", name="cart_show")
     */
    public function show(): Response
    {
        $form= $this->createForm(CartConfirmationType::class);

        $detailedCart= $this->cartService->getDetailedCartItems();

        $total= $this->cartService->getTotal();

        return $this->render('cart/index.html.twig', [
            'items' => $detailedCart,
            'total' => $total,
            'confirmationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id":"\d+"})
     */
    public function add($id, Request $request): Response
    {
        
        // Securisation : est-ce que le produit existe ?
        $product= $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas");
        }

        // Voir video 16.10
        $this->cartService->add($id);

        // Voir video 16.6
        // Pour gérer les messages :
        // en se faisant livrer « FlashBagInterface » :
        // $flashBag->add('success', "Le produit à bien été ajouté au panier");
        // sinon, grâce à « AbstractController » :
        $this->addFlash('success', "Le produit à bien été ajouté au panier");
        
        // dd($session->get('cart'), $session->getBag('flashes'));

        // Voir vidéo 16.13
        if ($request->query->get('returnToCart')) {
            return $this->redirectToRoute('cart_show');
            // Une autre option, utiliser la variable php server REFERER qui recharge l'URL précédente
            // return new RedirectResponse($request->headers->get('referer'));
        }
        
        return $this->redirectToRoute('product_show', [
            'product_slug' => $product->getSlug(),
            'category_slug' => $product->getCategory()->getSlug()
        ]);
    }

    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id":"\d+"})
     */
    public function decrement($id)
    {
        $product= $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas, il ne peut donc pas être soustrait !");
        }

        $this->cartService->decrement($id);

        $this->addFlash("success", "L'objet à bien été soustrait du panier");

        return $this->redirectToRoute('cart_show');

    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete", requirements={"id":"\d+"})
     */
    public function delete($id)
    {
        $product= $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas, il ne peut donc pas être supprimé !");
        }

        $this->cartService->remove($id);

        $this->addFlash("success", "L'objet à bien été supprimé du panier");

        return $this->redirectToRoute('cart_show');

    }


}
