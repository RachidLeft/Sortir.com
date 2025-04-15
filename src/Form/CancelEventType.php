<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;

class CancelEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $options['data'];

        $builder
            ->add('name', TextType::class, [
                'label'    => 'Nom de l\'événement',
                'disabled' => true,
                'data'     => $event->getName(),
            ])
            ->add('startDateTime', TextType::class, [
                'label'    => 'Date de l\'événement',
                'disabled' => true,
                'data'     => $event->getStartDateTime() ? $event->getStartDateTime()->format('d-m-Y H:i:s') : '',
            ])
            ->add('city', TextType::class, [
                'label'    => 'Ville',
                'disabled' => true,
                'mapped'   => false,
                'data'     => $event->getLocation() ? $event->getLocation()->getCityName() : '',
            ])
            ->add('location', TextType::class, [
                'label'    => 'Lieu',
                'disabled' => true,
                'mapped'   => false,
                'data'     => $event->getLocation() ? $event->getLocation()->getName() : '',
            ])
            ->add('motif', TextareaType::class, [
                'label'  => 'Motif d\'annulation',
                'mapped' => false,
                'required' => true,
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Annuler la sortie',
                'attr'  => [
                    'class' => 'btn btn-outline-danger btn-sm',
                ]
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}