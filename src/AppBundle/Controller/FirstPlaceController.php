<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Place;

class PlaceController extends Controller
{   

    /**
     * @Route("/test", name="test")
     * @Method("GET")
     */
    public function getTestAction(){

        $places = [
            ['id' => 1, 'lieu' => 'Maison', 'addresse' => 'Ville' ],
            ['id' => 2, 'lieu' => 'Chateau', 'addresse' => 'Campagne'],
            ['id' => 3, 'lieu' => 'piscine', 'addresse' => 'Plage'],
        ];

        $data = [
            'places' => $places
        ];

        return new JsonResponse ($data);  
    }


    /**
     * @Route("/test1", name="test1")
     * @Method({"GET"})
     */
    public function getTest1Action(Request $request)
    {
        $places=([
            new Place(01, "Tour Eiffel", "5 Avenue Anatole France, 75007 Paris"),
            new Place(02, "Mont-Saint-Michel", "50170 Le Mont-Saint-Michel"),
            new Place(03, "Château de Versailles", "Place d'Armes, 78000 Versailles"),
        ]);

        $formatted = [];

        foreach ($places as $value) {
            $formatted[] = [
               'id' => $value->getId(),
               'name' => $value->getName(),
               'address' => $value->getAddress(),
            ];
        }

        return new JsonResponse($formatted);

    }

    /**
     * @Route("/test2", name="test2")
     * @Method({"GET"})
     */
    public function getTest2Action(Request $request)
    {
        $places=([
            new Place(01, "Tour Eiffel", "5 Avenue Anatole France, 75007 Paris"),
            new Place(02, "Mont-Saint-Michel", "50170 Le Mont-Saint-Michel"),
            new Place(03, "Château de Versailles", "Place d'Armes, 78000 Versailles"),
        ]);

        $formatted= [
            'places' => $places
        ];

        return new JsonResponse($formatted);
    }

    /**
     * @Route("/places", name="places_list")
     * @Method({"GET"})
     */
    public function getPlacesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $places = $em->getRepository('AppBundle:Place')
                ->findAll();

        $formatted = [];

        foreach ($places as $value) {
            $formatted[] = [
                'id' => $value->getId(),
                'name' => $value->getName(),
                'address' => $value->getAddress(),
            ];
         }

        return new JsonResponse($formatted);
    }

    /**
     * @Route("/places/{place_id}", requirements={"id" = "\d+"}, name="places_one")
     * @Method({"GET"})
     */
    public function getPlaceAction(Request $request)
    {   
        $id = $request->get('place_id');
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository('AppBundle:Place')
                ->findOneById($id);

        if (empty($place)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }

        $formatted = [
           'id' => $place->getId(),
           'name' => $place->getName(),
           'address' => $place->getAddress(),
        ];

        return new JsonResponse($formatted);
    }
}