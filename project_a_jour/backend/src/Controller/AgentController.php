<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Form\AgentType;
use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/agents')] 
// resaka resend email confirmation aminn'ny agent ity satria nentsika vao manao creation agent tonga dia andefasana email automatique izy ilay any ambany fa eto koussa mandefa resend email
//fa tsy mbola confirmation fa mbola juste mandefa email resend ao amin'ny AgentConfirmationController vao misy ilay confirmation
//mmbola avant confirmation le ato 
class AgentController extends AbstractController
{
    #[Route('/{id}/resend-confirmation', name: 'app_agents_resend_confirmation')]
    public function resendConfirmation(Request $request, Agent $agent): Response
    {
        // Si le token est null ou vide, on le régénère
        if (!$agent->getConfirmationToken()) {
            $ref = new \ReflectionClass($agent);
            $method = $ref->getMethod('generateConfirmationToken');
            $method->setAccessible(true);
            $method->invoke($agent);
            $this->entityManager->flush();
        }
        // Ity ilay lien de confirmation halefa any amin'ilay agent
        $confirmationUrl = $this->generateUrl('app_agent_confirm', ['token' => $agent->getConfirmationToken() /*ity ConfirmationToken ity tadiavina nay amin'ny AgentConfirmationCOntroller*/], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

        $plainPassword = null;

        $html = $this->renderView('agent/email_confirmation.html.twig', [
            'agent' => $agent,
            'confirmationUrl' => $confirmationUrl,
            'plainPassword' => $plainPassword ? $plainPassword : 'Non communiqué pour des raisons de sécurité. Veuillez contacter l’administrateur si vous souhaitez le réinitialiser.',
        ]);
        $locale = $request->getLocale() ?? $this->getParameter('locale') ?? 'fr';
                $subject = 'Confirmation de votre compte agent';
                $email = (new Email())
                ->from('noreply@echeck-in.com')
                ->to($agent->getEmail())
                ->subject($subject)
                ->html($html);
try {
    $this->mailer->send($email);
    $this->addFlash('success', 'E-mail de confirmation renvoyé à l’agent avec succès');
} catch (\Exception $e) {
    $this->addFlash('danger', $this->translator->trans('agent.confirmation_error', ['%error%' => $e->getMessage()], 'messages', $locale));
}
        return $this->redirectToRoute('app_agents_show', ['id' => $agent->getId()]);
    }
    public function __construct(
    private EntityManagerInterface $entityManager,
    private AgentRepository $agentRepository,
    private UserPasswordHasherInterface $passwordHasher,
    private \Symfony\Component\Mailer\MailerInterface $mailer,
    private \Symfony\Contracts\Translation\TranslatorInterface $translator
) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        $agents = $this->agentRepository->findBy(['owner' => $this->getUser()], ['createdAt' => 'DESC']);

        return $this->render('agent/index.html.twig', [
            'agents' => $agents,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $agent = new Agent();
        $form = $this->createForm(AgentType::class, $agent, [
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($agent, $plainPassword);
                $agent->setPassword($hashedPassword);
            }

            $agent->setOwner($this->getUser());
            $this->entityManager->persist($agent);
            $this->entityManager->flush();

            // Eo amin'ilay creer agent misy fandefasana email avy hatrany 
            $confirmationUrl = $this->generateUrl('app_agent_confirm', ['token' => $agent->getConfirmationToken() /*ity ConfirmationToken ity averina ary amin'ny AgentCOnfirmationController satria ho tadiavin any aminn'y base*/], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $plainPassword = $plainPassword ?? $form->get('password')->getData();
            $html = $this->renderView('agent/email_confirmation.html.twig', [
                'agent' => $agent,
                'confirmationUrl' => $confirmationUrl,
                'plainPassword' => $plainPassword,
            ]);
            $email = (new \Symfony\Component\Mime\Email())
                ->from('noreply@echeck-in.com')
                ->to($agent->getEmail())
                ->subject('Confirmation de votre compte agent')
                ->html($html);
            $this->mailer->send($email);

            $this->addFlash('success', 'Agent créé avec succès. Un e-mail de confirmation a été envoyé.');
            return $this->redirectToRoute('app_agents_show', ['id' => $agent->getId()]);
        }

        return $this->render('agent/new.html.twig', [
            'agent' => $agent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Agent $agent): Response
    {
        return $this->render('agent/show.html.twig', [
            'agent' => $agent,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Agent $agent): Response
    {
        $form = $this->createForm(AgentType::class, $agent, [
            'password_required' => false,
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasehan ilay mot de passe raha nampidirina
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($agent, $plainPassword);
                $agent->setPassword($hashedPassword);
            }

            $agent->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Agent modifié avec succès');
            return $this->redirectToRoute('app_agents_show', ['id' => $agent->getId()]);
        }

        return $this->render('agent/edit.html.twig', [
            'agent' => $agent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Agent $agent): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agent->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($agent);
            $this->entityManager->flush();
            $this->addFlash('success', 'Agent supprimé avec succès.');
        }

        return $this->redirectToRoute('app_agents_index');
    }
}
