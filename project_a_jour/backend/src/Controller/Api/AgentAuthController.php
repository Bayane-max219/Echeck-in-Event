<?php

namespace App\Controller\Api;

use App\Entity\Agent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/agent', name: 'api_agent_')]
class AgentAuthController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]//miandry post avy amin'ilay application mobile
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        error_log('[AgentAuthController] LOGIN: email reçu=' . ($data['email'] ?? 'null') . ' | password reçu=' . ($data['password'] ?? 'null'));

        if (!$data || !isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email et mot de passe requis'], 400); 
            //laha tsy feno ilay champ email sy password dia mimpoly erreur 400 fona
        }

        $agent = $this->entityManager->getRepository(Agent::class)
            ->findOneBy(['email' => $data['email']]);

        if (!$agent) {
            error_log('[AgentAuthController] LOGIN: Aucun agent trouvé pour cet email');
            return new JsonResponse(['error' => 'Aucun agent trouvé pour cet email'], 404);
            //laha tsy hitany ilay agent any amin'ny base de donnée dia mimpoly erreur 404 fona
        } else {
            error_log('[AgentAuthController] LOGIN: Agent trouvé (id=' . $agent->getId() . ', email=' . $agent->getEmail() . ', is_confirmed=' . ($agent->isConfirmed() ? '1' : '0') . ')');
        }

        $isPasswordValid = $this->passwordHasher->isPasswordValid($agent, $data['password']);
        error_log('[AgentAuthController] LOGIN: Password valid? ' . ($isPasswordValid ? 'OUI' : 'NON'));
        if (!$isPasswordValid) {
            return new JsonResponse(['error' => 'Mot de passe incorrect'], 401);
        }
        //manamarina izy laha ohatry ka marina mot de passe iny fa laha diso manao erreur 401

        if (!$agent->isConfirmed()) {
            error_log('[AgentAuthController] LOGIN: Account not confirmed');
            return new JsonResponse(['error' => "Votre compte n'est pas encore confirmé. Vérifiez votre email."], 403);
        }
        //laha mbo tsy niconfirme agent iny de manheo erreur hoe iha mbo tsy niconfirme
        
        $event = $agent->getEvent(); // mangalaky evenement mifandray aminy agent iny 
        if (!$event) {
            error_log('[AgentAuthController] LOGIN: No event associated');
            return new JsonResponse(['error' => "Aucun événement n'est associé à votre compte. Contactez votre organisateur.", 'event_status' => null], 403);
        }
        //laha tsy misy evenement nifandray taminn'ny agent iny dia erreur
        //laha tsy active ilay evenement dia ireto miseho
        if ($event->getStatus() !== 'active') {
            error_log('[AgentAuthController] LOGIN: Event not active (status=' . $event->getStatus() . ')');
            $reason = '';
            switch ($event->getStatus()) {
                case 'draft':
                    $reason = "L'événement auquel vous êtes lié est en mode brouillon.";
                    break;
                case 'cancelled':
                    $reason = "L'événement auquel vous êtes lié a été annulé.";
                    break;
                case 'completed':
                    $reason = "L'événement auquel vous êtes lié est terminé.";
                    break;
                default:
                    $reason = "L'événement auquel vous êtes lié n'est pas actif (statut: " . $event->getStatus() . ")";
            }
            return new JsonResponse([
                'error' => $reason,
                'event_status' => $event->getStatus(),
            ], 403);//ato zany raha vao tsy active ilay evenement dia ireo no miseho 
        }

        // Laha ok iaby indray ny evenement izany hoe actif ndray 
        $token = $this->jwtManager->create($agent);//mamorona token identifiant ilay agent ilaina aminy api rehetra 
        //io ataony aminn'ny tableau ambany io 
        $agentArray = [
            'id' => $agent->getId(),
            'email' => $agent->getEmail(),
            'firstName' => $agent->getNom(),
            'lastName' => $agent->getNom(), // Remplir si champ existe
            'roles' => $agent->getRoles(),
            'createdAt' => $agent->getCreatedAt() ? $agent->getCreatedAt()->format('c') : null,
        ];
        //Mampimpoly valiny aminn'ny mobile ay amizay 
        return new JsonResponse([
            'message' => 'Connexion réussie',
            'token' => $token,
            'agent' => $agentArray
        ]);
    }
}
