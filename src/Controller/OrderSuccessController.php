<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/commande/merci/{stripeSessionId}", name="app_order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {

        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$order->isIsPaid()) {

            $cart->remove();

            $order->setIsPaid(1);
            $this->entityManager->flush();

            // Envoyer un email a notre client pour lui confirmer ça commande

            $mail = new Mail();
            $content = "Bonjour " . $order->getUser()->getFirstName() . "<br>Merci pour votre commande";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstName(), "Votre commande La Boutique Française est validée", $content);

        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);

    }
}
