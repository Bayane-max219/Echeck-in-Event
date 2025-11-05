<?php

namespace App\Form;

use App\Entity\Agent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Contracts\Translation\TranslatorInterface;

class AgentType extends AbstractType
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'common.full_name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom complet'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'common.email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'agent@example.com'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'email ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'common.password',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password',
                    'placeholder' => $options['password_required'] ? 'Entrez le mot de passe' : 'Laissez vide pour conserver le mot de passe actuel'
                ],
                'constraints' => $options['password_required'] ? [
                    new NotBlank([
                        'message' => 'Le mot de passe ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ] : [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
                'required' => $options['password_required'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'active',
                    'Inactif' => 'inactive',
                    'Suspendu' => 'suspended',
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'common.roles',
                'choices' => [
                    'Agent' => 'ROLE_AGENT',
                    'Superviseur' => 'ROLE_SUPERVISOR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check'
                ],
            ])
            ->add('event', EntityType::class, [
                'class' => \App\Entity\Event::class,
                'choice_label' => 'title',
                'label' => 'Événement',
                'placeholder' => 'Sélectionner un événement',
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => true,
                'query_builder' => function (\App\Repository\EventRepository $er) use ($options) {
                    return $er->createQueryBuilder('e')
                        ->where('e.organizer = :owner')
                        ->setParameter('owner', $options['current_user']);
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agent::class,
            'password_required' => true,
            'current_user' => null,
        ]);
        $resolver->setDefined('current_user');
    }
}
