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
class ActivityController extends AbstractController
{
 /**
     * @Route("/manage_activities", name="manage_activities")
     * @return mixed
     */
    public function manage_activities()
    {
        $activities = $this->getDoctrine()->getRepository('App:Activite')->findall();

        return $this->render('activity/manage_activities.html.twig', ['activities' => $activities]);
    }

    /**
     * @Route("/display_activity/{id}", name="display_activity")
     * @param $id
     * @return mixed
     */
    public function seeActivity($id)
    {
        $activity = $this->getDoctrine()->getRepository('App:Activite')->find($id);
        return $this->render('activity/display_activity.html.twig', ['activity' => $activity]);
    }

    /**
     * @Route("/form_activity/{id}", name="form_activity")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @return mixed
     */
    public function formActivity(Request $request, ObjectManager $om, $id)
    {
        if($id == -1)   // Ajout
        {
            $activity = new Activite();
        }
        elseif($id != -1)   // modif
        {
            $activity = $this->getDoctrine()->getRepository('App:Activite')->findOneBy(['id' => $id]);
        }

        $form_activity = "yo"; // A FAIRE -------------------------------------------------------------> Voir formUser()


        // Création du formulaire
        $form_activity = $this->createFormBuilder($activity)
            ->add('label')
            ->add('typeof')
            ->add('color',  ColorType::class)
            ->add('description')
            ->getForm();

        $form_activity->handleRequest($request);

        if($form_activity->isSubmitted() && $form_activity->isValid())
        {
            $om->persist($activity);
            $om->flush();

            return $this->redirectToRoute('manage_activities');
        }

        return $this->render('activity/form_activity.html.twig', ['form_activity' => $form_activity->createView(), 'id' => $id]);
    }

    /**
     * @Route("/delete_activity/{id}", name="delete_activity")
     */
    public function delActivity($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $activity = $em->getRepository('App:Activite')->find($id);
        $workon = $em->getRepository('App:Workon')->findBy(array('activite' => $id));
        foreach ($workon as $work) {
            $em->remove($work);
        }
        $em->remove($activity);
        $em->flush();

        return new RedirectResponse($this->generateUrl('manage_activities'));
    }

    /**
     * @Route("/display_personne_activities/{id}", name="display_personne_activities")
     * @param $id
     * @return mixed
     */
    public function seePersonneActivities($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('App:Personne')->find($id);
        $workon = $em->getRepository('App:Workon')->findBy(array('personne' => $id));


        //A terminer

        return $this->render('activity/display_personne_activities.html.twig', ['workon' => $workon, 'user' => $user]);
    }

    /**
     * @Route("/form_personne_activity/{id}", name="form_personne_activity")
     * @param Request $request
     * @param ObjectManager $om
     * @return mixed
     */
    public function formPersonneActivity(Request $request, ObjectManager $om, $id)
    {

        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('App:Personne')->find($id);
        $activites = $em->getRepository('App:Activite')->findAll();
        $workon = new Workon();

        $select_activities = [];

        /* Pour chaque activité on crée le tableau que l'on va passer
           en argument à 'choices' pour le select */
        foreach($activites as $activity)
        {
            $label_activite = $activity->getLabel();
            $select_activities[$label_activite] = $activity;
        }

        $form_personne_activity = $this->createFormBuilder($workon)
            ->add('activite', ChoiceType::class, [
                'choices'  => $select_activities,
            ])
            ->getForm();

        $form_personne_activity->handleRequest($request);

        if($form_personne_activity->isSubmitted() && $form_personne_activity->isValid())
        {
            $workon->setPersonne($user);
            $om->persist($workon);
            $om->flush();

            return $this->redirectToRoute('display_personne_activities', ['id' => $id]);
        }

        return $this->render('activity/form_personne_activity.html.twig', ['form_personne_activity' => $form_personne_activity->createView(), 'activites' => $activites, 'user' => $user]);
    }

    /**
     * @Route("/delete_personne_activity/{id}/{id_activite}", name="delete_personne_activity")
     * @param $id
     * @param $id_activite
     * @return mixed
     */
    public function delPersonneActivity($id, $id_activite)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workon = $em->getRepository('App:Workon')->find($id_activite);
        $em->remove($workon);
        $em->flush();

        return $this->redirectToRoute('activity/display_personne_activities', ['id' => $id]);
    }
}
