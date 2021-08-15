<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Entrez le nom du produit'],
                'required' => false,
                // 'constraints' => new NotBlank(['message' => '(dans ProductType.php) Le nom du produit est obligatoire'])
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description du produit',
                'attr' => ['placeholder' => 'Entrez une courte description du produit']
            ])
            ->add('price', MoneyType::class, [
                // 'currency' => false,
                'divisor' => 100,
                'label' => 'Prix du produit',
                'attr' => ['placeholder' => 'Entrez le prix du produit (en €)'],
                'required' => false,
                // 'constraints' => new NotBlank(['message' => '(dans ProductType.php) Le prix du produit est obligatoire'])
            ])
            ->add('mainPicture', UrlType::class, [
                'label' => 'Image du produit',
                'attr' => ['placeholder' => 'Entrez une URL d\'image'],
                'default_protocol' => ''
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie du produit',
                'placeholder' => '--- Choisir une catégorie ---',
                'class' => Category::class, // doit être suivi de « choice_label » pour fonctionner (sinon : erreur « Object of class App\Entity\Category could nit be converted to string »)
                'choice_label' => 'name' // function (Category $category) {return strtoupper($category->getName());} (pour mettre en majuscule par exemple)
            ]);
        }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
