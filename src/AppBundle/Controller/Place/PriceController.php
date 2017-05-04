<?php
namespace AppBundle\Controller\Place;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\PriceType;
use AppBundle\Entity\Price;

class PriceController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"price"})
     * @Rest\Get("/places/{id}/prices")
     */
    public function getPricesAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository('AppBundle:Place')
                ->findOneById($id);

        if (empty($place)) {
             return $this->placeNotFound();
        }

        return $place->getPrices();

    }

     /**
     * @Rest\View(statusCode=Response::HTTP_CREATED,serializerGroups={"price"})
     * @Rest\Post("/places/{id}/prices")
     */
    public function postPricesAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $place = $em->getRepository('AppBundle:Place')
                ->findOneById($id);

        if (empty($place)) {
             return $this->placeNotFound();
        }

        $price = new Price();
        $price->setPlace($place); 
        $form = $this->createForm(PriceType::class, $price);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em->persist($price);
            $em->flush();
            return $price;
        } else {
            return $form;
        }

    }

    private function placeNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
    }
}