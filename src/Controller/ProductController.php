<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController {
    /**
     * @Route("/{slug}", name="product_category", priority="-1")
     */
    public function category($slug, CategoryRepository $categoryRepository): Response
    {

        $category= $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category)
        {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas");
        }

        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }


    /**
     * @Route("/{category_slug}/{product_slug}", name="product_show")
     */
    public function show($category_slug, $product_slug, ProductRepository $productRepository): Response
    {

        $product= $productRepository->findOneBy([
            'slug' => $product_slug,
        ]);

        if (!$product)
        {
            throw $this->createNotFoundException("Le produit demandé n'existe pas");
        }
    
        // On redirige vers la bonne url si l'url est corrompue au niveau du category_slug avec la méthode RedirectToRoute
        $category_slug_foundProduct = $product->getCategory()->getSlug();
        if ($category_slug_foundProduct !== $category_slug) {
            return $this->redirectToRoute("product_show", [
                "category_slug" => $category_slug_foundProduct,
                "product_slug" => $product_slug
            ]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }


    /**
     * @Route("/admin/product/{id}/edit", name="product_edit")
     */
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em)
    {
        $product= $productRepository->find($id);

        $form= $this->createForm(ProductType::class, $product); // passer « $product » en paramètre revient à faire « $form->setData($product); »

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'product_slug' => $product->getSlug()
            ]);
        }  

        $formView= $form->createView();

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView
        ]);
    }


    /**
     * @Route("/admin/product/create", name="product_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {

        $product= new Product;

        $form= $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $product->setSlug(strtolower($slugger->slug($product->getName())));

            $em->persist($product);
            $em->flush();
            
            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'product_slug' => $product->getSlug()
            ]);
        }  

        $formView= $form->createView();

        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }
}
