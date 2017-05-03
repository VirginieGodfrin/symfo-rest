<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;


class UserController extends Controller
{   
    /**
     * @Rest\View
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')
                ->findAll();

        return $users;
    }

    /**
     * @Rest\View
     * @Rest\Get("/users/{id}")
     */
    public function getUserAction($id){

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
                ->findOneById($id);

        if (empty($user)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }

        return $user;

    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
             $em = $this->getDoctrine()->getManager();
             $em->persist($user);
             $em->flush();
             return $user;
        } else {
             return $form;
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/users/{id}")
     */
    public function removeUserAction($id,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
                    ->findOneById($id);
        /* @var $user User */

        if ($user) {
            $em->remove($user);
            $em->flush();
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/users/{id}")
     */
    public function updateUserAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
                    ->findOneById($id);
        /* @var $user User */
        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()){
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

        /**
         * @Rest\View()
         * @Rest\Patch("/users/{id}")
         */
        public function patchUserAction($id, Request $request)
        {
            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository('AppBundle:User')
                    ->findOneById($id);

            if (empty($user)) {
                return new JsonResponse(['message' => 'user not found'], Response::HTTP_NOT_FOUND);
            }

            $form = $this->createForm(UserType::class, $user);

            // Le paramètre false dit à Symfony de garder les valeurs dans notre
            // entité si l'utilisateur n'en fournit pas une dans sa requête
            $form->submit($request->request->all(), false);

            if ($form->isValid()) {
                    $em->merge($user);
                    $em->flush();
                    return $user;
                } else {
                    return $form;
                }

        }

}