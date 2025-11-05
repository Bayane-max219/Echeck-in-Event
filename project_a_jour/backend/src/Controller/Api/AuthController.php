<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api', name: 'api_')]

class AuthController
{
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return new JsonResponse(['error' => 'Champs obligatoires manquants'], 400);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse(['error' => 'Utilisateur déjà existant'], 409);
        }

        $user = new User();
        $user->setEmail($data['email'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        // Add ROLE_ADMIN for first user, ROLE_USER for others
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        if ($userCount === 0) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.',
            'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => 'user:read']))
        ], 201);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        
        return new JsonResponse([
            'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => 'user:read']))
        ]);
    }

    #[Route('/profile', name: 'update_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], 400);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Profil mis à jour avec succès',
            'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => 'user:read']))
        ]);
    }
}