<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ForgottenPasswordController extends AbstractController
{
    /**
     * @Route("/mot-de-passe-oublie", name="forgotten_password")
     */
    public function forgottenPassword(Request $request, UserRepository $userRepo, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        // On crée le formulaire
        $form = $this->createForm(ResetPassType::class);

        // On traite le formulaire
        $form->handleRequest($request);

        //Si le formulaire est valide
        if($form->isSubmitted() && $form->isValid()){

            // On cherche si un utilisateur a cet email
            $user = $userRepo->findOneByEmail($form->getData()['email']);

            // Si l'utiliateur n'existe pas
            if(!$user){
                $this->addFlash('danger', 'Cette adresse n\'existe pas');
                $this->redirectToRoute('sign_in');
            }

            // On génère un token
            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }catch(\Exception $e){
                $this->addFlash('warning', 'Une erreur est survenue : ' . $e->getMessage());
                $this->redirectToRoute('sign_in');
            }

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('reset_password',['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // On envoie le message
            $message = (new \Swift_Message('mot de passe'))
                ->setFrom('j.bros@hotmail.fr')
                ->setTo($user->getEmail())
                ->setBody(
                    "<p>Bonjour,</p><p>Une demande de réinitialisation de mot de pass a été effectué.</p></p>Veuillez cliquer sur le lien suivant : " . $url . "</p>",
                    'text/html'
                )
            ;
            // On envoie l'email
            $mailer->send($message);
            $this->addFlash('success', 'Un email de réinitialisation de mot de passe vous a été envoyé');
            $this->redirectToRoute('sign_in');
        }

        return $this->render('security/forgotten_password.html.twig',[
            'ForgottenPasswordForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("reset-password/{token}", name="reset_password")
     */
    public function resetPassword($token, Request $request, UserPasswordEncoderInterface $passwordEncoder){
        // On cherche l'utilisateur avec le token fourni
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        if(!$user){
            $this->addFlash('danger', 'Token inconnu');
            $this->redirectToRoute('sign_in');
        }

        // Si le formulaire est envoyé en méthode POST
        if($request->isMethod('POST')){
            // On supprime le token
            $user->setResetToken(null);

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès');
            return $this->redirectToRoute('sign_in');
            
        } else{
            return $this->render('security/reset_password.html.twig',['token' => $token]);
        }
    }
}
