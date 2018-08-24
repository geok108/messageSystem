<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Chatroom;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();


        $chatrooms = $this->getDoctrine()
            ->getRepository(Chatroom::class)
            ->findAll();


        return $this->render('home.html', array('chatrooms' => $chatrooms, 'users' => $users));


    }

    /**
     * @Route("/createchatroom", name="chatroom")
     */
    public function createChatroomAction(Request $request)
    {

        $chatroom = new Chatroom();

        $form = $this->createFormBuilder($chatroom)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create chatroom'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $chatroom = $form->getData();


            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($chatroom);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('chatroom_form.html', array(
            'form' => $form->createView(),
        ));

    }



    /**
     * @Route("/createchatroom", name="chatroom")
     */
    public function createUserAction(Request $request)
    {

        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create user'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $user = $form->getData();


            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user_form.html', array(
            'form' => $form->createView(),
        ));

    }
}
