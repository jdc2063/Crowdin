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

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choice_lang = $options['data']['data'];
        $builder
            ->add('lang_code', ChoiceType::class,
                [
                    'label'=>'Dans quelle langue exporter la traduction?',
                    'choices' => $choice_lang,
                    'choice_value' => function ($row) {
                        return $row;
                    },
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
