<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\Projet;
use App\Entity\TraductionTarget;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TraductionTargetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choice_lang = $options['data']['test'];
        $builder
            ->add('lang_code', ChoiceType::class,
                [
                    'label'=>'Dans quelle langue allez vous traduire la source?',
                    'choices' => $choice_lang,
                    'choice_value' => function ($row) {
                        return $row;
                    },
                ])
            ->add('traduction')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
