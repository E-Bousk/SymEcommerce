<?php

namespace App\EventDispatcher;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Event\PurchaseSuccessEvent;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseSuccessEmailSubscriber implements EventSubscriberInterface
{
    protected $logger;
    protected $mailer;
    // protected $security;

    public function __construct(LoggerInterface $logger,
                                // Security $security,
                                MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        // $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            'purchase.success' => 'sendSuccessEmail'
        ];
    }

    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseSuccessEvent)
    {
        //Voir vidéo 21.6
        // 1. Recuperer l'utilisateur en ligne (avec le service Security)
        // 1. Recuperer l'utilisateur en ligne (avec PurchaseSuccessEvent)
        /** @var User */
        // $currentUser= $this->security->getUser();
        $currentUser= $purchaseSuccessEvent->getPurchase()->getUser();

        // 2. Recuperer la commande (avec PurchaseSuccessEvent)
        $currentCommand= $purchaseSuccessEvent->getPurchase();

        // 3. Écrire le mail (avec TemplatedEmail)
        $email= new TemplatedEmail();

        $email->from(new Address("noreply@mail.com", "Info boutique"))
            ->to(new Address($currentUser->getEmail(), $currentUser->getFullName()))
            ->subject("Votre commande ({$currentCommand->getId()}) à bien été confirmée")
            ->htmlTemplate("emails/purchase_success.html.twig")
            ->context([
                'purchase' => $currentCommand,
                'user' => $currentUser
            ]);

        // 4. Envoyer le mail (avec MailerInterface)
        $this->mailer->send($email);

        // $this->logger->info("Email envoyé pour la commande n° " . $purchaseSuccessEvent->getPurchase()->getId());
    }
}