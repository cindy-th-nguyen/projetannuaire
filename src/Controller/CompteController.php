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
use App\Entity\Groupinfo;

/**
 * @isGranted("ROLE_USER")
 */
class CompteController extends AbstractController
{
/**
     * @Route("/display_compte/{id_compte}/{id}", name="display_compte")
     * @param $id_compte
     * @param $id
     * @return mixed
     */
    public function seeCompte($id_compte, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $compte = $em->getRepository('App:Compte')->find($id_compte);
        $user = $this->getDoctrine()->getRepository('App:Personne')->find($id);
        $role = $this->getDoctrine()->getRepository('App:Role')->find($compte->getRole());
        $groupinfo = $em->getRepository(Groupinfo::class)->find($compte->getGroupinfo());
        return $this->render('compte/display_compte.html.twig', [ 'user'=>$user, 'compte'=>$compte, 'role'=>$role, 'groupinfo'=>$groupinfo ]);
    }

    /**
     * @Route("/form_compte/{id}/{id_compte}", name="form_compte")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @param $id_compte
     * @return mixed
     */
    public function formCompte(Request $request, ObjectManager $om, $id, $id_compte, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if($id_compte == -1)   // Ajout
        {
            $compte = new Compte();
        }
        elseif($id_compte != -1)   // modif
        {
            $compte = $this->getDoctrine()->getRepository('App:Compte')->findOneBy(['id' => $id_compte]);
            
            
        }
        $user = $em->getRepository('App:Personne')->find($id);
        $group_infos = $em->getRepository(Groupinfo::class)->findAll();
        $select_groupinfo = [];
        
        foreach($group_infos as $group_info){
            $select_groupinfo[$group_info->getLabel()] = $group_info->getId();
        }
        
        // Création du formulaire
        $form_compte = $this->createFormBuilder($compte)
            ->add('login')
            ->add('password')
            ->add('groupinfo', ChoiceType::class, [
                'choices' => $select_groupinfo,
            ])
            ->add('role')
            ->add('startdate', DateType::class, [
                'years' => range(date('Y') -50, date('Y'))
            ])
            ->add('enddate', DateType::class, [
                'years' => range(date('Y'), date('Y') +20),
            ])
            ->getForm();

        $form_compte->handleRequest($request);
        $group_info_value = $form_compte['groupinfo']->getData();

        if($form_compte->isSubmitted() && $form_compte->isValid())
        {
            // dump($user);
            // die(0);
            $compte->setGroupInfo($group_infos[$group_info_value - 1]);
            $pass = $user->getCompte()->getPassword();
            $encoded = $encoder->encodePassword($user->getCompte(), $pass);
            $user->getCompte()->setPassword($encoded);
            $user->getCompte()->setLogin($request->request->get("form")["login"]);
            $user->setCompte($compte);
            $om->persist($compte);
            $om->flush();

            return $this->redirectToRoute('display_personne', ['id' => $id]);
        }
        return $this->render('compte/form_compte.html.twig', ['form_compte' => $form_compte->createView(), 'user' => $user]);
    }

    /**
     * @Route("/active_compte/{id_compte}/{id}", name="active_compte")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id_contrat
     * @param $id
     * @return mixed
     */
    public function activeCompte(Request $request, ObjectManager $om, $id_compte, $id)
    {
       $em = $this->getDoctrine()->getEntityManager();
       $compte = $em->getRepository('App:Compte')->find($id_compte);
       if ($compte->getActif()) {
        $compte->setActif(false);
       } else {
        $compte->setActif(true);
       }
       $em->flush();
       return $this->redirectToRoute('personne/display_personne', ['id' => $id]);
    }
}
