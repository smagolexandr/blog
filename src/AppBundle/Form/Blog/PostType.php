<?php

namespace AppBundle\Form\Blog;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => "Название",
                'required' => true,
                'attr' => array('class' => 'form-control')
            ))
            ->add('content', TextareaType::class, array(
                'label' => "Контент",
                'required' => true,
                'attr' => array('class' => 'form-control', 'rows' => "10")
            ))
            ->add('tags', EntityType::class, array(
                'class' => 'AppBundle\Entity\Blog\Tag',
                'choice_label' => 'name',
                'multiple' => true,
                'placeholder' => 'Введите теги',
                'label' => "Теги",
                'required' => false,
                'attr' => array('class' => 'form-control chosen-select')
            ))
            ->add('imageFile', FileType::class, array(
                'required' => false,
                'attr' => array('class' => 'form-control')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AppBundle\Entity\Blog\Post']);
    }
}