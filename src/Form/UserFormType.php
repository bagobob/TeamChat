<?php


namespace App\Form;



use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName',HiddenType::class)
            ->add('lastName',HiddenType::class)
            ->add('userName',HiddenType::class)
            ->add('imageFile',VichImageType::class,[
                'required' => false,
                'allow_delete' => false,
                'label' => 'choisir',
                'download_uri' => '',
                'download_label' => false,
                'imagine_pattern' => 'squared_thumbnail_small',
                'attr' => [
                    'class' => 'custom-file-input'
                ]
            ])
            ->add('submit',SubmitType::class,[
                'label' => 'Mettre à jour photo',
                'attr' => [
                    'class' => 'btn'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }


}