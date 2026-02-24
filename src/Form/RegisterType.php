<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ImageFile', FileType::class, [
                'required' => false,
                'mapped' => true,
                'label' => 'Photo de profil (jpg, png, gif, webp - max 2 Mo)',
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
            ->add('username')
            ->add('email')
            ->add('password')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
