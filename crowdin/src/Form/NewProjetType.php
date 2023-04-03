<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\Projet;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lang_code', EntityType::class,
                [
                    'label'=>'Dans quelle langue est votre projet?',
                    'class' => Lang::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    },
                    'choice_label' => function ($category) {
                        return $category->getName();
                    },
                    'choice_value' => function (?Lang $entity) {
                        return $entity ? $entity->getCode() : '';
                    },
                ])
            ->add('name')
            ->add('lang_has_projet', EntityType::class,
                [
                    'label'=>'Dans quelles langues votre projet devraient être traduit?',
                    'multiple'=>true,
                    'class' => Lang::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    },
                    'choice_label' => function ($category) {
                        return $category->getName();
                    },
                    'choice_value' => function (?Lang $entity) {
                        return $entity ? $entity->getCode() : '';
                    },
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
