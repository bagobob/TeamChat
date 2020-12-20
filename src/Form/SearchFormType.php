<?php


namespace App\Form;

use App\Search;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string',TextType::class,[
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'rechercher...',
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('submit',SubmitType::class,[
                'label' => 'Chercher',
                'attr' => [
                    'class' => 'btn-block btn-info'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return '' ;
    }

}