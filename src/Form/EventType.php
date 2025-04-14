<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\Status;
use App\Entity\User;
use App\Form\LocationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                ]
            )
            ->add('startDateTime', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
            ])
            ->add('registrationDeadline', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('maxRegistration', IntegerType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en minutes)',
            ])
            ->add('info', TextareaType::class, [
                'label' => 'Description',

            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label'=> function (Location $location) {
                    return $location->getName() . ' - ' . $location->getStreet() . ', ' . $location->getPostalCode() . ' ' . $location->getCityName();

                },
                'label' => 'Lieu',
                'placeholder' => 'Sélectionnez un lieu',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.name', 'ASC');
                },
               'required'=> false,
            ])
            ->add('newLocation', LocationType::class, [
                'label' => 'Ajouter un nouveau lieu',
                'required' => false,
                'mapped' => false, // Ce champ n'est pas directement lié à l'entité Event
            ])
        ;
        $builder ->add('enregistrer', SubmitType::class, [
            'label' => 'Enregistrer',
        ])
            ->add('publier', SubmitType::class, [
            'label' => 'Publier',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
