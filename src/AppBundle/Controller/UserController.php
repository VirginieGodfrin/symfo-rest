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
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')
                ->findAll();

        return $users;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{id}")
     */
    public function getUserAction($id){

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
                ->findOneById($id);

        if(empty($user)){
            return $this->userNotFound();
        }

        return $user;

    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED,serializerGroups={"user"})
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user,['validation_groups'=>['Default', 'New']]);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            // appel au service encoder de symfo
            $encoder = $this->get('security.password_encoder');
            // le mot de passe en claire est encodé avant la sauvegarde
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"user"})
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

    // /**
    //  * @Rest\View()
    //  * @Rest\Put("/users/{id}")
    //  */
    // public function updateUserAction($id, Request $request)
    // {
    //     $em = $this->getDoctrine()->getManager();
    //     $user = $em->getRepository('AppBundle:User')
    //                 ->findOneById($id);
    //     /* @var $user User */
    //     if(empty($user)){
    //         return $this->userNotFound();
    //     }

    //     $form = $this->createForm(UserType::class, $user);

    //     $form->submit($request->request->all());

    //     if ($form->isValid()){
    //         $em->merge($user);
    //         $em->flush();
    //         return $user;
    //     } else {
    //         return $form;
    //     }
    // }

    ///**
    // * @Rest\View()
    // * @Rest\Patch("/users/{id}")
    // */
    // public function patchUserAction($id, Request $request)
    // {
    //     $em = $this->getDoctrine()->getManager();

    //     $user = $em->getRepository('AppBundle:User')
    //             ->findOneById($id);

    //     if(empty($user)){
    //         return $this->userNotFound();
    //     }

    //     $form = $this->createForm(UserType::class, $user);

    //     // Le paramètre false dit à Symfony de garder les valeurs dans notre
    //     // entité si l'utilisateur n'en fournit pas une dans sa requête
    //     $form->submit($request->request->all(), false);

    //     if ($form->isValid()) {
    //         $em->merge($user);
    //         $em->flush();
    //         return $user;
    //     } else {
    //         return $form;
    //     }

    // }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/users/{id}")
     */
    public function updateUserAction(Request $request)
    {
        return $this->updateUser($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Patch("/users/{id}")
     */
    public function patchUserAction(Request $request)
    {
        return $this->updateUser($request, false);
    }


    private function updateUser(Request $request, $clearMissing){
        
        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');
        $user = $em->getRepository('AppBundle:User')
                ->findOneById($id);

        if(empty($user)){
            return $this->userNotFound();
        }

        if($clearMissing){
            // Si une mise à jour complète, le mot de passe doit être validé
            $option = ['validation_groups'=>['Default','FullUpdate']];
            //'default' regroupe toutes les contraintes de validation qui ne sont dans aucun groupe
        }else{
            // Le groupe de validation par défaut de Symfony est Default
            $option = [];
        }

        $form = $this->createForm(UserType::class, $user, $options);

        $form->submit($request->request->all(), $clearMissing);

        if($form->isValid()){
            // Si l'utilisateur veut changer son mot de passe
            if(!empty($user->getPlainPassword())){
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($encoded);
            }
            $em->merge($user);
            $em->flush();
            return $user;
        }else{
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"place"}) // la méthode renvoie des place !
     * @Rest\Get("/users/{id}/suggestions")
     */
    public function getUserSuggestionAction($id, Request $request){

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')
                ->findOneById($id);

        if(empty($user)){
            return $this->userNotFound();
        }

        $suggestions = [];

        $places = $em->getRepository('AppBundle:Place')
                ->findAll();

        foreach ($places as $place) {
            if($user->preferencesMatch($place->getThemes())){
                $suggestions[]= $place;
            }
        }

        return $suggestions;
    }

    private function userNotFound()
    {
        // return \FOS\RestBundle\View\View::create(['message' => 'User not found'], 
        //     Response::HTTP_NOT_FOUND);
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
    }

}