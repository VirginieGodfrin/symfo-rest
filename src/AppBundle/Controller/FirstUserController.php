<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use AppBundle\Entity\User;

class UserController extends Controller
{   
    /**
     *
     * @Get("/users")
     */
    public function getUsersAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')
                ->findAll();

        $data = [];

        foreach ($users as $value) {
            $data[] = [
                'id' => $value->getId(),
                'firstname' => $value->getFirstname(),
                'lastname' => $value->getLastname(),
                'email' => $value->getEmail()
            ];
         }

        return new JsonResponse($data);
    }

    /**
     * @Get("/users/{id}")
     */
    public function getUserAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
                ->findOneById($id);

        if (empty($user)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $value->getId(),
            'firstname' => $value->getFirstname(),
            'lastname' => $value->getLastname(),
            'email' => $value->getEmail()
        ];

        return new JsonResponse($data);

    }

}