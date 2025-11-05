<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')] // routte ilay login
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');//raha raha connecté tonga dia dirigé vers dashboard
        }

        $error = $authenticationUtils->getLastAuthenticationError();//mangalaky ereur farany laha nisy diso taminy ogin ohatry mot de passe
        $lastUsername = $authenticationUtils->getLastUsername();// maka izay deja nampidiirina averina eo aminn'ny formulaire

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error, // aseho @ ecran raha misy erreur
        ]);
    }

    #[Route('/register', name: 'app_register')] // route ilay register creation de compte
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,//ireto parametre ireto ilaina aminn'ny création*/
        EntityManagerInterface $entityManager,//de compte request; hash password, base*/
        TranslatorInterface $translator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        } // tsy avela hisoratra anarana ilay user lafa connecté fa tonga de alefa mainy dashboard any */

        if ($request->isMethod('POST')) { //laha ohatry ka manao post alatsika iaby ny info nampidiriny taminy formulaire*/
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');

            // Validation du mot de passe (minimum 6 caractères)
            if (strlen($password) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->render('security/register.html.twig', [
                    'last_email' => $email,
                    'last_firstName' => $firstName,
                    'last_lastName' => $lastName
                ]);
            }

            // laha tsy @gmail.com nampiliriny maneho erreur lery
            if (!preg_match('/^[^@\s]+@gmail\.com$/', $email)) {
                $this->addFlash('error', $translator->trans('auth.email_must_be_gmail'));
                return $this->render('security/register.html.twig', [
                    'last_email' => $email,
                    'last_firstName' => $firstName,
                    'last_lastName' => $lastName
                ]);
            }

            // lafa email fa nisy maneho erreur lery
            $existingUser = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($existingUser) {
                $this->addFlash('error', $translator->trans('auth.account_exists'));
                return $this->render('security/register.html.twig', [
                    'last_email' => $email,
                    'last_firstName' => $firstName,
                    'last_lastName' => $lastName
                ]);
            }
//mamorona objet vaovao tsika eto anara; mot de passe izay hashé 
            $user = new User();
            $user->setEmail($email)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setPassword($passwordHasher->hashPassword($user, $password))
                ->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Indian/Antananarivo')));

            // Laha vao io no user voalohanhy toga dia omena anarana ADMIN
            $userCount = $entityManager->getRepository(User::class)->count([]);
            if ($userCount === 0) {
                $user->setRoles(['ROLE_ADMIN']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('auth.account_created_success'));
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {   //Tsy mila manoratra code manokana tsika, fa lafa mikaiky logout dia deconnecté automatique ilay user
        //tsy mila manao code zany ato fa a ny Symfony firewall (configuration security.yaml) no mi-intercept sy mi-logout automatique
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();//maka ny utilisateur connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');//laha tsy misy user connecté de mimpoly aminy login izay ny logique fa raha mbola connecté mou izy d emijano eo amin'ny page profile
        }
        return $this->render('security/profile.html.twig', [
            'user' => $user,
        ]);
    }
}