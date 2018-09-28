<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SignInForm
 * @package App\Form
 */
class SignInForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'maxlength' => 34,
                    'minlength' => 6
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length([
                        'min' => 6,
                        'max' => 34,
                    ])
                ]
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'maxlength' => 24,
                    'minlength' => 8,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'max' => 24,
                    ])
                ]
            ])
            ->add('signIn', SubmitType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => '/sign-in',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'sign_in.token',
        ]);
    }
}
