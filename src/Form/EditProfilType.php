<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EditProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Description de l\'utilisateur',
                    'class' => 'form-area',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'required' => false,
                'mapped' => true,
                'label' => 'Photo de profil',
                'attr' => [
                    'accept' => '.jpg,.jpeg,.png,.gif,.webp',
                ],
                'constraints' => [
                    new Assert\File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Seuls les formats jpg, png, gif et webp sont autorisés.',
                        maxSizeMessage: 'Le fichier est trop gros (max 2 Mo).',
                    ),
                ],
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
