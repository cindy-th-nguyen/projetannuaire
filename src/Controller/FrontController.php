<?php

namespace App\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Personne;
use App\Entity\Contrat;
use App\Entity\Activite;
use App\Entity\Compte;
use App\Entity\Workon;
use App\Entity\Tutelle;
use App\Entity\Groupinfo;

/**
 * @isGranted("ROLE_USER")
 */
class FrontController extends AbstractController
{
    /**
     * @Route("/annuaire", name="annuaire")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('App:Personne')->findAll();
        return $this->render('front/index.html.twig', [ 'users' => $users]);
    }

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
        return $this->render('front/display_personne.html.twig', [
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
            ->add('nationality')
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
                        'mimeTypesMessage' => 'Veuillez entrer une image dans un format valide',
                    ])
                ],
            ])
            ->add('civilite', ChoiceType::class, [
                'choices'  => [
                    'Monsieur' => 'Monsieur',
                    'Madame' => 'Madame'
                ],
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
           
            $user->setBuilding($buildings[$building_value]);
            $user->setOffice($offices[$office_value]);

            $om->persist($user);
            $om->flush();

            return $this->redirectToRoute('annuaire');
        }
        return $this->render('front/form_user.html.twig', ['form_personne' => $form_personne->createView(), 'id' => $id, 'tutelle' => $tutelles]);
    }

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
         return $this->render('front/display_contrat.html.twig', ['contrat' => $contrat, 'user' => $user, 'type' => $type_contrat]);
     }
    
    /**
     * @Route("/form_thematique/", name="form_thematique")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @return mixed
     */
    public function form_thematique()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $thematiques = $em->getRepository('App:Thematique')->findall();

        return $this->render('front/form_thematique.html.twig', ['thematiques' => $thematiques]);
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

        return $this->render('front/form_contrat.html.twig', ['form_contrat' => $form_contrat->createView(), 'user' => $user, 'id_contrat' => $id_contrat]);
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

    /**
     * @Route("/import-csv", name="import-csv")
     * @param Request $request
     * @return mixed
     */
    public function import(Request $request)
    {
        $n=0;
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('post')) {
           foreach ($request->files as $filename){
               $path = $filename->getPathName();
                $file = fopen("$path", "r");
                while (($column = fgetcsv($file, 1024, ",")) !== FALSE) {
                    if ($n > 0)
                    {
                        $compte = new Compte();
                        $compte->setLogin($column[7]);
                        $compte->setPassword('azerty');
                        $em->persist($compte);
                        $em->flush();
                        $personne = new Personne();
                        $dateBirth = \DateTime::createFromFormat('d/m/Y', $column[3]);
                        $dateArriv = \DateTime::createFromFormat('d/m/Y', $column[14]);
                        $dateDepart = \DateTime::createFromFormat('d/m/Y', $column[15]);
                        $personne
                            ->setFirstname($column[0])
                            ->setLastname($column[1])
                            ->setCivilite($column[2])
                            ->setBirthdate($dateBirth)
                            ->setNationality($column[4])
                            ->setPlacebirth($column[5])
                            ->setMail($column[6])
                            ->setMailGeeps($column[7])
                            ->setHomephone($column[8])
                            ->setMobilephone($column[9])
                            ->setOffice($column[10])
                            ->setBuilding($column[11])
                            ->setTutelle($column[12])
                            ->setIngeeps($column[13])
                            ->setArrivaldate($dateArriv)
                            ->setDeparturedate($dateDepart)
                            ->setCompte($compte);
                        $em->persist($personne);
                        $em->flush();
                    }
                    $n+=1;
                }
                fclose($file);
            }
        }
        return $this->render('front/import.html.twig');
    }

    private function parsePersonArray($listPerson){

    }

    /**
     * @Route("/export", name="export")
     * @return mixed
     */
    public function export(Request $request)
    {
        $users = $this->getDoctrine()->getRepository('App:Personne')->findall();
        dump("aaaaa");
        if ($request->isMethod('post')) {
            $selectedUsers = array();
            if(isset($_POST['checkAll'])){
                $selectedUsers = $users;
            }else{
                foreach ($users as $user){
                    $id = $user->getId();
                    if(isset($_POST['check' . $id])){
                        array_push($selectedUsers, $user);
                    }
                }
            }
            dump("df");
            dump($_POST['exportType']);
            dump(isset($_POST['exportType']));
            if(isset($_POST['exportType']) &&  (strcmp($_POST['exportType'], 'CSV') !== 0)){

                $pdfOptions = new Options();
                $pdfOptions->set('defaultFont', 'Arial');
                $pdfOptions->set('isRemoteEnabled', true);
                $pdfOptions->set('isHtml5ParserEnabled', true);

                // Instantiate Dompdf with our options
                $dompdf = new Dompdf($pdfOptions);

                // Retrieve the HTML generated in our twig file
                $html = $this->renderView('front/pdf_personne.html.twig', ['users'=>$selectedUsers]);

                // Load HTML to Dompdf
                $dompdf->loadHtml($html);

                // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
                $dompdf->setPaper('A3', 'landscape');

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser (inline view)
                $dompdf->stream("export_" . date("d_m_Y") . ".pdf", [
                    "Attachment" => true
                ]);


            }else{
                $filename = "export_" . date("d_m_Y") . ".csv";
                dump("dfdf");
                dump($selectedUsers);
                if (count($selectedUsers) > 0) {

                    $response = new StreamedResponse();
                    $response->setCallback(function() use ($selectedUsers){
                        $df = fopen("php://output", 'w+');
                        fputcsv($df, array('Prénom',
                            'Nom de famille',
                            'Date de naissance',
                            'Lieu de naissance',
                            'Email' ,
                            'E-Mail GeePs',
                            'Tél. fixe',
                            'Tél. mobile',
                            'Bureau',
                            'Bâtiment',
                            'Tutelle',
                            'Est du laboratoire',
                            'Date d\'arrivée',
                            'Date de départ'));
                        foreach ($selectedUsers as $user) {
                            if($user->getBirthdate() != null) {
                                $date1 = $user->getBirthdate()->format('d/m/Y');
                            }else{
                                $date1 = null;
                            }
                            if($user->getArrivaldate() != null){
                                $date2 = $user->getArrivaldate()->format('d/m/Y');
                                $date3 = $user->getDeparturedate()->format('d/m/Y');
                            }else{
                                $date2 = null;
                                $date3 = null;
                            }
                            fputcsv($df, array($user->getFirstname(),
                                $user->getLastname(),
                                $date1,
                                $user->getPlacebirth(),
                                $user->getMail(),
                                $user->getMailGeeps(),
                                $user->getHomephone(),
                                $user->getMobilephone(),
                                $user->getOffice(),
                                $user->getBuilding(),
                                $user->getTutelle(),
                                $user->getIngeeps(),
                                $date2,
                                $date3));
                        }
                        fclose($df);
                    });

                    $response->setStatusCode(200);
                    $response->headers->set('Content-Type', 'text/csv; charset=utf-8', 'application/force-download');
                    $response->headers->set('Content-Disposition','attachment; filename='.$filename);

                    return $response;
                }
            }

        }
        return $this->render('front/export.html.twig', ['users'=>$users]);
    }

    /**
     * @Route("/manage_activities", name="manage_activities")
     * @return mixed
     */
    public function manage_activities()
    {
        $activities = $this->getDoctrine()->getRepository('App:Activite')->findall();

        return $this->render('front/manage_activities.html.twig', ['activities' => $activities]);
    }

    /**
     * @Route("/display_activity/{id}", name="display_activity")
     * @param $id
     * @return mixed
     */
    public function seeActivity($id)
    {
        $activity = $this->getDoctrine()->getRepository('App:Activite')->find($id);
        return $this->render('front/display_activity.html.twig', ['activity' => $activity]);
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

        return $this->render('front/form_activity.html.twig', ['form_activity' => $form_activity->createView(), 'id' => $id]);
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

        return $this->render('front/display_personne_activities.html.twig', ['workon' => $workon, 'user' => $user]);
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

        return $this->render('front/form_personne_activity.html.twig', ['form_personne_activity' => $form_personne_activity->createView(), 'activites' => $activites, 'user' => $user]);
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

        return $this->redirectToRoute('display_personne_activities', ['id' => $id]);
    }

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
        return $this->render('front/display_compte.html.twig', [ 'user'=>$user, 'compte'=>$compte, 'role'=>$role, 'groupinfo'=>$groupinfo ]);
    }

    /**
     * @Route("/form_compte/{id}/{id_compte}", name="form_compte")
     * @param Request $request
     * @param ObjectManager $om
     * @param $id
     * @param $id_compte
     * @return mixed
     */
    public function formCompte(Request $request, ObjectManager $om, $id, $id_compte)
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
            ->add('role')
            ->add('groupinfo', ChoiceType::class, [
                'choices' => $select_groupinfo,
            ])
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
            $compte->setGroupInfo($group_infos[$group_info_value - 1]);
            $user->setCompte($compte);
            $om->persist($compte);
            $om->flush();

            return $this->redirectToRoute('display_personne', ['id' => $id]);
        }
        return $this->render('front/form_compte.html.twig', ['form_compte' => $form_compte->createView(), 'user' => $user, 'groupinfo' => $group_infos]);
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
       return $this->redirectToRoute('display_personne', ['id' => $id]);
    }
}
