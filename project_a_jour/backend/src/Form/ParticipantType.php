<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'common.first_name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'common.last_name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'common.email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(\+?(261|33|32|44|1)[0-9]{8,13}|0[0-9]{9,10})$/',
                        'message' => 'Numéro de téléphone invalide.'
                    ]),
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'common.company',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('position', TextType::class, [
                'label' => 'common.position',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('sendInvitation', CheckboxType::class, [
                'label' => 'Envoyer une invitation par e-mail',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}