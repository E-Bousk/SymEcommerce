<?php

namespace App\Cart;

use App\Cart\CartItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService 
{
    protected $session;
    protected $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session= $session;
        $this->productRepository= $productRepository;
    }

    // Voir vidéo 16.14
    protected function getCart() : array
    {
        return $this->session->get('cart', []);
    }

    protected function saveCart(array $cart)
    {
        $this->session->set('cart', $cart);
    }

    public function empty() 
    {
        $this->saveCart([]);
    }

    public function add(int $id) 
    {
        // Voir video 16.1 à 16.3 & 16.10
        // 1. Retrouver le panier dans la session
        //   (sous forme de tableau: Ex. [12 => 1, 29 =>2])
        // 2. Si il n'existe pas, alors prendre un tableau vide
        $cart= $this->getCart();

        // 3.Voir si le produit ($id) existe déjà dans le tableau
        // 4. Si oui, augmenter la quantité
        // 5. Si non, ajouter le produit avec quantité = 1
        // if (array_key_exists($id, $cart)) {
        //     $cart[$id]++;
        // } else {
        //     $cart[$id]= 1;
        // }

        // Voir vidéo 16.14
        if (!array_key_exists($id, $cart)) {
            $cart[$id]= 0;
        }
        $cart[$id]++;

        // 6. enregistrer le tableau mis à jour dans la session
        $this->saveCart($cart);

        // pour vider la session :
        // $this->session->remove('cart');
    }

    public function remove(int $id)
    {
        $cart= $this->getCart();

        unset($cart[$id]);

        $this->saveCart($cart);
    }
    
    public function decrement(int $id)
    {
        $cart= $this->getCart();

        if (!array_key_exists($id, $cart)) {
            return;
        }
        
        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }

        $cart[$id]--;

        $this->saveCart($cart);
    }

    public function getTotal() : int
    {
        $total= 0;
        foreach ($this->getCart() as $id => $qty) {
            $product= $this->productRepository->find($id);

            // Voir vidéo 16.11
            if (!$product) {
                continue;
            }

            $total += $product->getPrice()*$qty;
        }
        return $total;
    }
    
    // Voir vidéo 17.11 à 4:55
    /**
     * @return cartItem[]
     */
    public function getDetailedCartItems() : array
    {
        $detailedCart= [];
        
        foreach ($this->getCart() as $id => $qty) {
            $product= $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $detailedCart[]=  new cartItem($product, $qty);
        }

        // dd($detailedCart);
        return $detailedCart;

        // dd($this->session->get('cart'), $detailedCart);
    }
}