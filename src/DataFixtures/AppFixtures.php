<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
// use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture {

    // protected $slugger;
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder,
                                // SluggerInterface $slugger
    ) {
        // $this->slugger= $slugger;
        $this->encoder= $encoder;
    }

    public function load(ObjectManager $manager) {
        
        $faker= Factory::create('fr_FR');
        $faker->addProvider(new \Liior\Faker\Prices($faker));
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
        $faker->addProvider(new \Bluemmb\Faker\PicsumPhotosProvider($faker));


        $admin= new User;
        $admin->setEmail("admin@gmail.com")
            ->setPassword($this->encoder->encodePassword($admin, "root"))
            ->setFullName("admin")
            ->setRoles(['ROLE_ADMIN'])
        ;

        $manager->persist($admin);

        $users=[];

        for ($u= 0; $u < 5; $u++) {
            $user= new User;
            $user->setEmail($faker->email())
                ->setFullName($faker->name())
                ->setPassword($this->encoder->encodePassword($user, "password"))
            ;

            $users[]= $user;

            $manager->persist($user);
        }

        $products=[];

        for ($c= 0; $c < 3; $c++) {
            $category= new Category;

            $category->setName($faker->department)
                // voir vidéo 20.7
                // ->setSlug(strtolower($this->slugger->slug($category->getName())))
            ;

            $manager->persist($category);

            for ($p= 0; $p < mt_rand(15, 20); $p++) {
                $product= new Product;
    
                $product->setName($faker->productName)
                   ->setPrice($faker->price(4000, 20000))
                // voir vidéo 20.3
                //    ->setSlug(strtolower($this->slugger->slug($product->getName())))
                   ->setCategory($category)
                   ->setShortDescription($faker->paragraph())
                   ->setMainPicture($faker->imageUrl(400, 400, true))
                ;
        
                $products[]= $product;

                $manager->persist($product);
            }
        }

        for($p= 0; $p < mt_rand(20, 40); $p++) {
            $purchase= new Purchase;

            $purchase->setFullName($faker->userName)
                ->setAddress($faker->streetAddress)
                ->setPostalCode($faker->postcode)
                ->setCity($faker->city)
                ->setUser($faker->randomElement($users))
                ->setPurchasedAt($faker->dateTimeBetween('-6 months'))
            ;
            
            $totalItems= 0;

            // Voir vidéo 17.5
            $selectedproducts= $faker->randomElements($products, mt_rand(3, 5));


            foreach($selectedproducts as $product) {
                $purchaseItem = new PurchaseItem;
                $purchaseItem->setProduct($product)
                    ->setQuantity(mt_rand(1, 5))
                    ->setProductName($product->getName())
                    ->setProductPrice($product->getPrice())
                    ->setTotal(
                        $purchaseItem->getProductPrice() * $purchaseItem->getQuantity()
                    )
                    ->setPurchase($purchase)
                ;

                $totalItems += $purchaseItem->getTotal();

                $manager->persist($purchaseItem);
            }

            $purchase->setTotal($totalItems);

            if($faker->boolean(90)) {
                $purchase->setStatus(Purchase::STATUS_PAID);
            }

            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
