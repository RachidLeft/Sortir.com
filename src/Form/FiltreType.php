<?php

namespace App\Form;

use App\Entity\Site;
use App\Model\Filtre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class,[
                'class'=> Site::class,
                'choice_label'=>'name',
                'required' => false,
            ])
            ->add('search', SearchType::class,[
                'required' => false,

            ])
            ->add('startDateTime', DateTimeType::class,[
                'required' => false,
                'widget' => 'single_text',

            ])
            ->add('registrationDeadline', DateTimeType::class,[
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('organizer', CheckboxType::class,[
                'label'    => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
            ])
            ->add('isRegister', CheckboxType::class,[
                'label'    => 'Sorties auxquelles je suis inscrit(e)',
                'required' => false,
            ])
            ->add('unRegister', CheckboxType::class,[
                'label'    => 'Sorties auxquelles je ne suis pas inscrit(e)',
                'required' => false,
            ])
            ->add('isPast', CheckboxType::class,[
                'label'    => 'Sorties passées',
                'required' => false,
            ])
            ->add('rechercher', SubmitType::class, ['label' => 'Rechercher']);

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filtre::class,
        ]);
    }

}