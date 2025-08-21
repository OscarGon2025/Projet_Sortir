<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription',
            ])
            ->add('nbInscriptionsMax')
            ->add('infoSortie')
//            ->add('etat', HiddenType::class, [
//            'mapped' => true,
//         ])
            ->add('siteOrganisateur', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom', // ajusta al campo visible de tu entidad Site
                'placeholder' => 'Sélectionnez un site',
                'required' => true,
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom', // ajusta si tu entidad Lieu usa otro campo
                'placeholder' => 'Seleccione un lugar',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}