<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest; //allias pour toutes les annotations
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View; // Utilisation de la vue de FOSRestBundle
use AppBundle\Entity\Place;
use AppBundle\Form\PlaceType;

class PlaceController extends Controller
{   
    /**
     * @Rest\View()
     * @Rest\Get("/places")
     */
    public function getPlacesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $places = $em->getRepository('AppBundle:Place')
                ->findAll();

        //Récupération du view handler
        //$viewHandler = $this->get('fos_rest.view_handler');
        //création d'une vue FosRestBundle
        //suite à format_listener:
            // rules:
            //     - { path: '^/', priorities: ['json'], fallback_format: 'json' }
            // il n'est plus utile de créer la vue
        //$view = View::create($data);
        //$view->setFormat('json');
        //gestion de la réponse
        //return $viewHandler->handle($view);
        //return $view;

        return $places;

        //return new JsonResponse($formatted);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/places/{id}")
     */
    public function getPlaceAction(Request $request)
    {   
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository('AppBundle:Place')
                ->findOneById($id);

        if (empty($place)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }
        return $place;

    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/places")
     */
    public function postPlacesAction(Request $request)
    {
        // test payload
        // return [
        //     'payload' => [
        //         $request->get('name'),
        //         $request->get('address')
        //      ]
        // ];

        // return [
        // 'payload' => json_decode($request->getContent(), true)
        // ];

        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);
        //Donc pour mieux répondre aux contraintes REST, 
        //au lieu d’utiliser la méthode handleRequest pour soumettre le formulaire, 
        //nous avons opté pour la soumission manuelle avec submit.
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($place);
            $em->flush();
            return $place;
        } else {
            return $form;
        }
        
    }

        /**
         * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
         * @Rest\Delete("/places/{id}")
         */
        public function removePlaceAction($id,Request $request){
            $em = $this->getDoctrine()->getManager();
            $place = $em->getRepository('AppBundle:Place')
                        ->findOneById($id);
            if ($place) {
                $em->remove($place);
                $em->flush();
            }
        }

        
        /**
         * @Rest\View()
         * @Rest\Put("/places/{id}")
         */
        public function updatePlaceAction($id, Request $request)
        {
            return $this->updatePlace($id,$request, true);
        }

        /**
         * @Rest\View()
         * @Rest\Patch("/places/{id}")
         */
        public function patchPlaceAction($id, Request $request)
        {
            return $this->updatePlace($id, $request, false);
        }


        private function updatePlace($id, Request $request, $clearMissing)
        {
            $em = $this->getDoctrine()->getManager();
            $place = $em->getRepository('AppBundle:Place')
                        ->findOneById($id);

        if (empty($place)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PlaceType::class, $place);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em->persist($place);
            $em->flush();
            return $place;
        } else {
            return $form;
        }
    }
}