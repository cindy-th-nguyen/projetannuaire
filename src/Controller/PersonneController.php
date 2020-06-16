<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Workon;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


use App\Entity\Personne;
use App\Entity\Contrat;
use App\Entity\Activite;

/**
 * @isGranted("ROLE_USER")
 */
class PersonneController extends AbstractController
{
    /**
     * @Route("/display_personne/{id}", name="display_personne")
     * @param $id
     * @return mixed
     */
    public function seeUser($id)
    {
        $user = $this->getDoctrine()->getRepository('App:Personne')->find($id);
        $contrat = $this->getDoctrine()->getRepository('App:Contrat')->findOneBy(['personne' => $id]);
        $compte = $this->getDoctrine()->getRepository('App:Compte')->findOneBy(['id' => $user->getCompte()]);
        return $this->render('personne/display_personne.html.twig', ['user' => $user, 'contrat' => $contrat, 'compte' => $compte]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delUser($id)
    {
      $em = $this->getDoctrine()->getEntityManager();
      $personne = $em->getRepository('App:Personne')->find($id);
      $workon = $em->getRepository('App:Workon')->findBy(array('personne' => $id));
      $compte = $em->getRepository('App:Compte')->findOneBy(array('id' => $personne->getCompte()));
      $contrat = $em->getRepository('App:Contrat')->findOneBy(array('personne' => $id));
      foreach ($workon as $work) {
        $em->remove($work);
      }
      if($compte) {
          $em->remove($compte);
      }
      if($contrat) {
          $em->remove($contrat);
      }
      $em->remove($personne);
      $em->flush();

      return new RedirectResponse($this->generateUrl('annuaire'));
    }

    /**
     * @Route("/form_personne/{id}", name="form_personne")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @return mixed
     */
    public function formUser(Request $request, ObjectManager $om, $id)
    {   
        if($id == -1)   // Ajout
        {
            $user = new Personne();
        }
        elseif($id != -1)   // modif
        {
            $user = $this->getDoctrine()->getRepository('App:Personne')->findOneBy(['id' => $id]);
        }

        // Création du formulaire
        $form_personne = $this->createFormBuilder($user)
            ->add('firstname')
            ->add('lastname')
            ->add('birthdate', DateType::class, [
                'years' => range(date('Y') -90, date('Y') -15)
            ])
            ->add('placebirth')
            ->add('mail')
            ->add('mail_geeps')
            ->add('homephone')
            ->add('mobilephone')
            ->add('ingeeps')
            ->add('nationality')
            ->add('arrivaldate', DateType::class, [
                'years' => range(date('Y') -50, date('Y'))
            ])
            ->add('departuredate', DateType::class, [
                'years' => range(1, 40),
            ])
            ->add('img', FileType::class, ['required' => false])
            ->add('civilite', ChoiceType::class, [
                'choices'  => [
                    'Monsieur' => 'Monsieur',
                    'Madame' => 'Madame'
                ],
            ])
            ->add('office', ChoiceType::class, [
                'choices'  => [
                    '1' => '1',
                    '2' => '2'
                ],
            ])
            ->add('building', ChoiceType::class, [
                'choices'  => [
                    'LRI' => 'LRI',
                    'Gustave Eiffel' => 'Gust. Eiffel',
                    'Bâtiment 1' => 'Bat. 1'
                ],
            ])
            ->add('tutelle', ChoiceType::class, [
                'choices'  => [
                    '0' => '0',
                ],
            ])
            ->getForm();

        $form_personne->handleRequest($request);

        if($form_personne->isSubmitted() && $form_personne->isValid())
        {
            $om->persist($user);
            $om->flush();

            return $this->redirectToRoute('annuaire');
        }

        return $this->render('front/form_user.html.twig', ['form_personne' => $form_personne->createView(), 'id' => $id]);
    }
}
