<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Contracts\Translation\TranslatorInterface;

class EventType extends AbstractType
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'événement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date et heure de fin',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => 'draft',
                    'Actif' => 'active',
                    'Terminé' => 'completed',
                    'Annulé' => 'cancelled'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('menu', TextareaType::class, [
                'label' => 'Menu / Restauration prévue (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => "Exemple :\nEntrée : Salade composée (contient œuf, lait)\nPlat principal : Filet de poisson, riz basmati (contient poisson)\nDessert : Tarte aux pommes (contient gluten)\nBoissons : Jus de fruits, vin rouge\n[PORC] [VEGAN] [ALLERGÈNE: GLUTEN]"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}