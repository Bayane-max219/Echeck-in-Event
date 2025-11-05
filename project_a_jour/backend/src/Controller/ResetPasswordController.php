<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordController extends AbstractController
{
    //eto izy le hampiditra an'ny le compte email ary handefa azy aminn'ny email asina an'ilay lien izay hidirigena azy amin'ny formulaire hampidirany an'ilay mot de passe vaovao 
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(
        Request $request, //ireto Service ilaina amin’ny formulaire, base, mail, traduction.
        EntityManagerInterface $em,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);//Mamorona formulaire amin’ny alalan’ny type ResetPasswordRequestFormType.
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Aucun compte trouvé avec cette adresse email.');
            } else {
                // Mamorona token vaovao (UUID na uniqid) ho an’ny reset password.
                //Mamorona token amin’ny format UUID v4 (oh: b2a9e9c0-1f0f-4b9b-8e7d-2b7b2e9c7a0d) ho an'ny sécurité
                if (class_exists('Symfony\Component\Uid\Uuid')) {
                    $token = \Symfony\Component\Uid\Uuid::v4();
                } else {
                    $token = uniqid('reset_', true);
                }
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));//mametraka date dans 1h lany
                $em->flush();

                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('no-reply@echeckin-event.com', 'Echeck-in Event'))
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'resetToken' => $token,//lien misy token  reset paswrod
                        'user' => $user,
                    ]);
                $mailer->send($emailMessage);

                $this->addFlash('success', "Un email de réinitialisation a été envoyé si l'adresse existe.");
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }
//ity ilay rehefa mameno ilay formulaire misy nouveau mot de passe sy mot de passe de confirmation misy champ roa iny
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,//nasina parametre token satrria avy amin'ilay email reset passpowrd ialy Token avy amin’ny email no ampiasaina hitadiavana user.
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        //Raha tsy misy user na efa lany daty ilay token (plus de 1 heure), dia redirect any amin’ny page mot de passe oublié, miaraka amin’ny erreur.
        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('danger', 'Lien de réinitialisation invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }
        //Raha mety: aseho formulaire hanovana mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();
            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
            } else {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
                $user->setResetToken(null);//Esorina ny token sy expiration (tsy azo ampiasaina intsony
                $user->setResetTokenExpiresAt(null);
                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('reset_password/reset.html.twig', [//msiy formulaire nouveau mot de passe sy mot de passe de confirmation
            'resetForm' => $form->createView(),
        ]);
    }
} 