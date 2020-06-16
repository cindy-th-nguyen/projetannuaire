<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Workon;
use Doctrine\Common\Persistence\ObjectManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
class FrontController extends AbstractController
{
    /**
     * @Route("/annuaire", name="annuaire")
     */
    public function index()
    {
      $users = $this->getDoctrine()->getRepository('App:Personne')->findall(); //->findOneBy(['lastname'=>'Dupont'])
        return $this->render('front/index.html.twig', ['users'=>$users]);
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
}
