<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('lastname')
            ->add('firstname')
            ->add('phone')
            // ->add('active')
            ->add('username')
//             ->add('isRegister', EntityType::class, [
//                 'class' => Event::class,
// 'choice_label' => 'id',
// 'multiple' => true,
//             ])
//             ->add('isAttached', EntityType::class, [
//                 'class' => Site::class,
// 'choice_label' => 'id',
//             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
