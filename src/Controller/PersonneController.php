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
use App\Entity\Civilite;
use App\Entity\Pays;
use App\Entity\Tutelle;
use App\Entity\Contrat;
use App\Entity\Activite;
use Symfony\Component\Validator\Constraints\File;

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
        $thematique = $this->getDoctrine()->getRepository('App:Thematique')->findOneBy(['id' => $id]);
        $compte = $this->getDoctrine()->getRepository('App:Compte')->findOneBy(['id' => $user->getCompte()]);
        return $this->render('personne/display_personne.html.twig', [
            'user' => $user,
            'contrat' => $contrat,
            'compte' => $compte,
            'themathique' => $thematique
        ]);
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
        $em = $this->getDoctrine()->getEntityManager();

        if($id == -1){
            $user = new Personne();
        }
        elseif($id != -1) {
            $user = $em->getRepository(Personne::Class)->findOneBy(['id' => $id]);
        }

        // Récuperer table civilite
        $civilites = $em->getRepository(Civilite::class)->findAll();
        $select_civilites = [];

        foreach($civilites as $civilite){
            $select_civilites[$civilite->getLabel()] = $civilite->getId();
        }

        // Récuperer table pays
        $countries = $em->getRepository(Pays::class)->findAll();
        $select_countries = [];

        foreach($countries as $country){
            $select_countries[$country->getLabel()] = $country->getId();
        }

        // Récuperer table tutelle
        $tutelles = $em->getRepository(Tutelle::class)->findAll();
        $select_tutelles = [];

        foreach($tutelles as $tutelle){
            $select_tutelles[$tutelle->getLabel()] = $tutelle->getId();
        }

        // Récupérer table office
        $offices = $this->getDoctrine()->getRepository('App:Office')->findAll();
        $select_offices = [];

        foreach($offices as $office){
            $select_offices[$office->getLabel()] = $office->getId();
        }

        // Récupérer table building
        $buildings = $this->getDoctrine()->getRepository('App:Building')->findAll();
        $select_buildings = [];

        foreach($buildings as $building){
            $select_buildings[$building->getLabel()] = $building->getId();
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
            ->add('nationality', ChoiceType::class, [
                'choices'  => $select_countries
            ])
            ->add('arrivaldate', DateType::class, [
                'years' => range(date('Y') -50, date('Y'))
            ])
            ->add('departuredate', DateType::class, [
                'years' => range(1, 40),
            ])
            ->add('img', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => "Extension d'image invalide !",
                    ])
                ],
            ])
            ->add('civilite', ChoiceType::class, [
                'choices'  => $select_civilites
            ])
            ->add('office', ChoiceType::class, [
                'choices'  => $select_offices,
            ])
            ->add('building', ChoiceType::class, [
                'choices'  => $select_buildings,
            ])
            ->add('tutelle', ChoiceType::class, [
                'choices' => $select_tutelles,
            ])
            ->getForm();

        $form_personne->handleRequest($request);

        $tutelle_value = $form_personne['tutelle']->getData();
        $building_value = $form_personne['building']->getData();
        $office_value = $form_personne['office']->getData();
        $civilite_value = $form_personne['civilite']->getData();
        $country_value = $form_personne['nationality']->getData();

        if($form_personne->isSubmitted() && $form_personne->isValid())
        {
            $file = $form_personne->get('img')->getData();

            if($file != null) {
                $uploads_directory = $this->getParameter('uploads_directory');
                $filename = md5(uniqid()) . '.'. $file->guessExtension();
                $file->move(
                    $uploads_directory,
                    $filename
                );
                $user->setImg($filename);
            }

            $user->setTutelle($tutelles[$tutelle_value]);
            $user->setCivilite($civilites[$civilite_value]);
            $user->setBuilding($buildings[$building_value]);
            $user->setOffice($offices[$office_value]);
            $user->setNationality($countries[$country_value]);
            $om->persist($user);
            $om->flush();

            return $this->redirectToRoute('annuaire');
        }
        return $this->render('front/form_user.html.twig', ['form_personne' =>
            $form_personne->createView(),
            'id' => $id,
            'tutelle' => $tutelles,
            'civilite' => $civilites,
            'nationality' => $countries
        ]);
    }
}
