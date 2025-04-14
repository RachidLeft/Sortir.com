<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;

class CancelEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cancel', SubmitType::class, [
            'label' => 'Annuler la sortie',
            'attr'  => [
                'class' => 'btn btn-outline-danger btn-sm',
                //'style' => 'background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline;'
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