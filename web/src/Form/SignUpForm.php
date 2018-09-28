<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SignUpForm
 * @package App\Form
 */
class SignUpForm extends AbstractType
{
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
            ->add('firstName', TextType::class, [
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 24,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'max' => 24,
                    ])
                ],
            ])
            ->add('surname', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 4,
                        'max' => 32,
                    ])
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
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
                ],
                'second_options' => [
                    'label' => 'Repeated password',
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
                ]
            ])
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    'Man' => 0,
                    'Women' => 1,
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('dateBirth', BirthdayType::class, [
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('RegisterAccount', SubmitType::class)
        ;

        $builder->get('firstName')->addModelTransformer(new CallbackTransformer(
            function (?string $firstName) {
                return $firstName;
            },
            function (?string $firstName) {
                return htmlspecialchars($firstName);
            }
        ));

        $builder->get('surname')->addModelTransformer(new CallbackTransformer(
            function (?string $surname) {
                return $surname;
            },
            function (?string $surname) {
                return htmlspecialchars($surname);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => '/register',
            'csrf_token_id' => '_token.create.user',
            'csrf_field_name' => '_token',
            'csrf_protection' => true,
        ]);
    }
}
