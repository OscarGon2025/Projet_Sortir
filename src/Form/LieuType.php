<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez le nom du lieu'],
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez la rue'],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez la ville'],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: 75000'],
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => true,
                'scale' => 6,
                'attr' => ['placeholder' => 'Ex: 48.8566'],
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => true,
                'scale' => 6,
                'attr' => ['placeholder' => 'Ex: 2.3522'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
