<?php

namespace App\EventDispatcher;

use Psr\Log\LoggerInterface;
use App\Event\ProductViewEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ProductViewEmailSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $mailer;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'product.view' => 'sendEmail'
        ];
    }

    public function sendEmail(ProductViewEvent $productViewEvent)
    {
        // $email= new TemplatedEmail();

        // $email->from(new Address("noreply@mail.com", "Info boutique"))
        //     ->to("admin@gmail.com")
        //     ->text("Bla bla le produit : " . $productViewEvent->getProduct()->getName())
        //     ->htmlTemplate("emails/product_view.html.twig")
        //     ->context([
        //         'product' => $productViewEvent->getProduct()
        //     ])
        //     ->subject("Visite de produit ! => " . $productViewEvent->getProduct()->getName());

        // $this->mailer->send($email);

        $this->logger->info("Email envoyé pour la vue du produit : " . $productViewEvent->getProduct()->getName());
    }
}