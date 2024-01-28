<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class,[
                "mapped" => false,
                "constraints" => [
                    new NotBlank(),
                    new NotNull(),
                    new Length(min:8, max:30, minMessage: 'Il faut au moins 8 caractères.', maxMessage: 'Il faut maximum 30 caractères.'),
                    new Regex('#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,30}$#', message:'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.')
                ]
            ] )
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('avatar', FileType::class, [
                "mapped"=> false,
                "constraints"=> [
                    new File(maxSize : '10M', extensions : ['jpg', 'png'], maxSizeMessage: "Le fichier dépasse les 10 MO. >_<", extensionsMessage: 'Seul les formats jpg et png sont autorisés')
                ]
            ])
            ->add('inscription',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
