<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'Pseudo'
            ])
            ->add('firstname', null, [
                'label' => 'Prénom'
            ])
            ->add('lastname', null, [
                'label' => 'Nom'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone'
            ])
            ->add('email', null, [
                'label' => 'Email'
            ])
            ->add('picture', FileType::class, [
                'label' => 'Ma photo',
                'mapped' => false,
                'required' => false,
                ]
            )
            ->add('isAttached', EntityType::class, [
                'class' => Site::class,
                'label' => 'Site de rattachement',
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
