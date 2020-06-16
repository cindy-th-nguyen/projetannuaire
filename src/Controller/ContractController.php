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
class ContractController extends AbstractController
{

    /**
     * @Route("/display_contrat/{id_contrat}/{id}", name="display_contrat")
     * @param $id_contrat
     * @param $id
     * @return mixed
     */
    public function seeContrat($id_contrat, $id)
    {
        $contrat = $this->getDoctrine()->getRepository('App:Contrat')->find($id_contrat);
        $user = $this->getDoctrine()->getRepository('App:Personne')->find($id);
        $type_contrat = $this->getDoctrine()->getRepository('App:Typeofcontrat')->find($contrat->getType($id_contrat));
        return $this->render('contract/display_contrat.html.twig', ['contrat' => $contrat, 'user' => $user, 'type' => $type_contrat]);
    }

    /**
     * @Route("/form_contrat/{id}/{id_contrat}", name="form_contrat")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @param $id_contrat
     * @return mixed
     */
    public function formContrat(Request $request, ObjectManager $om, $id, $id_contrat)
    {
        if($id_contrat == -1)   // Ajout
        {
            $contrat = new Contrat();
        }
        elseif($id_contrat != -1)   // modif
        {
            $contrat = $this->getDoctrine()->getRepository('App:Contrat')->findOneBy(['id' => $id_contrat]);
        }

        $user = $this->getDoctrine()->getRepository('App:Personne')->find($id);

        // Création du formulaire
        $form_contrat = $this->createFormBuilder($contrat)
            ->add('personne', HiddenType::class, [
                'data' => $id
            ])
            ->add('subject')
            ->add('funding')
            ->add('director')
            ->add('administrator')
            ->add('homeorganization')
            ->add('salary')
            ->add('securite_social')
            ->add('startdate', DateType::class, [
                'years' => range(date('Y') -50, date('Y'))
            ])
            ->add('enddate', DateType::class, [
                'years' => range(date('Y'), date('Y') +20),
            ])
            ->add('type')
            ->getForm();

        $form_contrat->handleRequest($request);

        if($form_contrat->isSubmitted() && $form_contrat->isValid())
        {
            $contrat->setPersonne($user);
            $om->persist($contrat);
            $om->flush();

            if($id_contrat == -1)   // Ajout
            {
                return $this->redirectToRoute('display_personne', ['id' => $id]);
            }
            elseif($id_contrat != -1)   // modif
            {
                return $this->redirectToRoute('display_contrat', ['id_contrat' => $id_contrat, 'id' => $id]);
            }
        }

        return $this->render('contract/form_contrat.html.twig', ['form_contrat' => $form_contrat->createView(), 'user' => $user, 'id_contrat' => $id_contrat]);
    }


    /**
     * @Route("/delete_contrat/{id_contrat}/{id}", name="delete_contrat")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id_contrat
     * @param $id
     * @return mixed
     */
    public function delContrat(Request $request, ObjectManager $om, $id_contrat, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $contrat = $em->getRepository('App:Contrat')->find($id_contrat);
        $em->remove($contrat);
        $em->flush();
        return $this->redirectToRoute('display_personne', ['id' => $id]);
    }
}
