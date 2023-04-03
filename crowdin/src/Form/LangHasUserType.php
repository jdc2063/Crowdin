<?php

namespace App\Form;

use App\Entity\Lang;
use App\Entity\LangHasUser;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LangHasUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choice_lang = $options['data'];
        $builder
            ->add('lang_code', ChoiceType::class,
                [
                    'label'=>'Quelle langue maitrisez vous?',
                    'choices' => $choice_lang,
                    'choice_value' => function ($row) {
                        return $row;
                    },
                    'data_class'=>null
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => null,
        ]);
    }
}
