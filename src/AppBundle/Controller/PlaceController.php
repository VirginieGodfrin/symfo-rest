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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;



class PlaceController extends Controller
{   
    /**
     * @ApiDoc(
     *    description="Récupère la liste des lieux de l'application",
     *    output= { "class"=Place::class, "collection"=true, "groups"={"place"} }
     * )
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/places")     
     * @QueryParam(name="offset", requirements="\d+", default="", description="Index de début de la pagination")
     * @QueryParam(name="limit", requirements="\d+", default="", description="Index de fin de la pagination")
     * @QueryParam(name="sort", requirements="(asc|desc)", nullable=true, description="Ordre de tri (basé sur le nom)")
     */
    public function getPlacesAction(Request $request, ParamFetcher $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $sort = $paramFetcher->get('sort');


        $em = $this->getDoctrine()->getManager();

        $qb= $em->createQueryBuilder();
        $qb->select('p')
            ->from('AppBundle:Place', 'p');

        if ($offset != "") {
            $qb->setFirstResult($offset);
        }

        if ($limit != "") {
            $qb->setMaxResults($limit);
        }

        if (in_array($sort, ['asc', 'desc'])) {
            $qb->orderBy('p.name', $sort);
        }

        $places = $qb->getQuery()->getResult();

        return $places;


        //$places = $em->getRepository('AppBundle:Place')
        //       ->findAll();

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
    }

    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/places/{id}")
     */
    public function getPlaceAction(Request $request)
    {   
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository('AppBundle:Place')
                ->findOneById($id);

        if (empty($place)) {
            // return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
            return $this->placeNotFound();
        }
        return $place;

    }

    /**
     * @ApiDoc(
     *    resource=true,
     *    description="Crée un lieu dans l'application",
     *    input={"class"=PlaceType::class, "name"=""},
     *    statusCodes = {
     *        201 = "Création avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Place::class, "groups"={"place"}},
     *         400 = { "class"=PlaceType::class, "fos_rest_form_errors"=true, "name" = ""}
     *    }
     * )
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"place"})
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

            foreach ($place->getPrices() as $price) {
                $price->setPlace($place);
                $em->persist($price);
            }
            
            $em->persist($place);
            $em->flush();
            return $place;
        } else {
            return $form;
        }
        
    }

        /**
         * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"place"})
         * @Rest\Delete("/places/{id}")
         */
        public function removePlaceAction($id,Request $request){
            $em = $this->getDoctrine()->getManager();
            $place = $em->getRepository('AppBundle:Place')
                        ->findOneById($id);

            if(!$place){
                return;
            }
            
            foreach($place->getPrices() as $price){
                $em->remove($price);
            }
            $em->remove($place);
            $em->flush();
        }

        
        /**
         * @Rest\View(serializerGroups={"place"})
         * @Rest\Put("/places/{id}")
         */
        public function updatePlaceAction($id, Request $request)
        {
            return $this->updatePlace($id,$request, true);
        }

        /**
         * @Rest\View(serializerGroups={"place"})
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
            // return \FOS\RestBundle\View\View::create(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
            return $this->placeNotFound();
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

    private function placeNotFound()
    {
        // return \FOS\RestBundle\View\View::create(['message' => 'User not found'], 
        //     Response::HTTP_NOT_FOUND);
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Place not found');
    }
}