<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Repository\ChambreRepository;
use App\Repository\HotelRepository;
use DateInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(){
        date_default_timezone_set("Africa/Casablanca");
    }
    /**
     * @Route("/", name="home")
     * @param Request $request
     * @param ChambreRepository $chambreRepository
     * @param HotelRepository $hotelRepository
     * @return Response
     */
    public function index(Request $request, ChambreRepository $chambreRepository , HotelRepository $hotelRepository): Response
    {
        $form = $this->createFormBuilder()
            ->add('destination', TextType::class)
            ->add('checkin', DateType::class, [
                'widget' => 'single_text',
               // 'data' => new \DateTime(),
            ])
            ->add('checkout', DateType::class, [
                'widget' => 'single_text',
               // 'data' => (new \DateTime())->add(new DateInterval('P1D')),
            ])
            ->add('guests', ChoiceType::class, [
                'choices'  => [
                    'any' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4+'=> 4,
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $rooms = $chambreRepository->findByInputs($data['destination'],$data['checkin'],$data['checkout'],$data['guests']);
            return $this->render('home/rooms.html.twig', ['form' => $form->createView(),
                'rooms' => $rooms,
            ]);
        }

        $RecommendedHotels = $hotelRepository->findRecommendedHotels();
        return $this->render('home/index.html.twig', [
            'form' => $form->createView(), 'RecommendedHotels' => $RecommendedHotels ,
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('home/about.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact()
    {
        return $this->render('home/contact.html.twig');
    }

    /**
     * @Route("/hotel/{hotelid}/rooms", name="hotelroomsAvailable")
     * @param int $hotelid
     * @param ChambreRepository $chambreRepository
     * @return Response
     */
    public function hotelroomsAvailable(Request $request, int $hotelid, HotelRepository $hotelRepository, ChambreRepository $chambreRepository): Response
    {
        $hotelname = $hotelRepository->find($hotelid)->getNom();

        $form = $this->createFormBuilder()
            ->add('destination', TextType::class, [
                'data' => $hotelname,
            ])
            ->add('checkin', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('checkout', DateType::class, [
                'widget' => 'single_text',
                'data' => (new \DateTime())->add(new DateInterval('P1D')),
            ])
            ->add('guests', ChoiceType::class, [
                'choices'  => [
                    'any' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4+' => 4,
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $rooms = $chambreRepository->findByInputs($data['destination'],$data['checkin'],$data['checkout'],$data['guests']);
            return $this->render('home/rooms.html.twig', ['form' => $form->createView(),
                'rooms' => $rooms,
            ]);
        }
        $rooms = $chambreRepository->findByInputs($hotelname, new \DateTime(), (new \DateTime())->add(new DateInterval('P1D')) , 0);

        dd($rooms);

        return $this->render('home/rooms.html.twig', ['form' => $form->createView(),
            'rooms' => $rooms,
        ]);
    }

    /**
     * @Route("/rooms/{id}/details/{checkin}/{checkout}", name="roomDetails")
     * @param Request $request
     * @param int $id
     * @param \DateTime $checkin
     * @param \DateTime $checkout
     * @param ChambreRepository $chambreRepository
     */
    public function details(Request $request, int $id, \DateTime $checkin, \DateTime $checkout ,ChambreRepository $chambreRepository)
    {

        $form = $this->createFormBuilder()
            ->add('name' , TextType::class)
            ->add('email', EmailType::class)
            ->add('tele' , TelType::class , [
                'required' => false,
            ])
            ->add('cinPass' , TextType::class)
            ->add('checkin' , DateType::class , [
                'data' => $checkin,
                'disabled' => true,
                'widget' => 'single_text',
            ])
            ->add('checkout', DateType::class , [
                'data' => $checkout,
                'disabled' => true,
                'widget' => 'single_text',
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        $chambreValid = $chambreRepository->isChambreAvailable($id , $checkin , $checkout);

        if($chambreValid == false || ($checkin > $checkout) || ($checkin == $checkout) ) {
            return $this->redirectToRoute('home');
        } elseif ($chambreValid == true) {
            $chambre = $chambreRepository->find($id);
            return $this->render('home/room.html.twig' , ['chambre' => $chambre ,
                'form' => $form->createView() ,
            ]);
        }
    }


    /**
     * @Route("/hotel/{hotelid}/roomsgallery", name="hotelrooms")
     * @param int $hotelid
     * @param ChambreRepository $chambreRepository
     * @return Response
     */
    public function hotelroomsGallery(int $hotelid, ChambreRepository $chambreRepository): Response
    {
        $rooms = $chambreRepository->findChambresByHotel($hotelid);
        $hotel = $rooms[0]->getHotel()->getNom();

        return $this->render('home/hotelrooms.html.twig' , ['rooms' => $rooms ,
            'hotel' => $hotel,
        ]);
    }

    /**
     * @Route("/rooms/{id}/gallery", name="roomgallery")
     */
    public function roomgallery(Chambre $chambre)
    {
        return $this->render('home/roomgallery.html.twig' , compact($chambre));
    }

}
