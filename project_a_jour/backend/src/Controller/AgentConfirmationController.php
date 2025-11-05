<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//resaka manov ailay egent ho confirmé
class AgentConfirmationController extends AbstractController
{
    #[Route('/agent/confirm/{token}', name: 'app_agent_confirm')]
    public function confirm(string $token, AgentRepository $agentRepository, EntityManagerInterface $em): Response
    {
        $agent = $agentRepository->findOneBy(['confirmationToken'/*ity ConfirmationToken ity averina ary amin'ny nalefany Agentcontroller taminy nandefa invitation aminy agent iny satria ho tadiavin any aminn'y base*/ => $token]);
        if (!$agent) {
            throw $this->createNotFoundException('Lien de confirmation invalide ou expiré.');
        }
        if ($agent->isConfirmed()) {
            return $this->render('agent/confirmation_already_confirmed.html.twig', [
                'agent' => $agent,
            ]);
        }
        //Raha mbola tsy confirmé:Manova ny agent ho “confirmed” (setIsConfirmed(true)).Manala ilay token (tsy azo ampiasaina intsony
        $agent->setIsConfirmed(true);
        $agent->setConfirmationToken(null);
        $em->flush();
        return $this->render('agent/confirmation_success.html.twig', [
            'agent' => $agent,
        ]);
    }
}
