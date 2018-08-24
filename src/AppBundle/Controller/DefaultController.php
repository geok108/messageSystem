<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Chatroom;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
     * @Route("/user/id={id}", name="user_home")
     */
    public function userHomeAction(Request $request, $id)
    {

        $chatrooms = $this->getDoctrine()
            ->getRepository(Chatroom::class)
            ->findAll();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);


        $message = new Message();
        $form = $this->createFormBuilder($message)
            ->add('context_text', TextareaType::class, array('label' => " "))
            ->add('save', SubmitType::class, array('label' => 'Send message to this chatroom'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $timestamp = new \DateTime("now");

            $message = $form->getData();
            $message->setSenderUser($id);
            $message->setType(1);
            $message->setTimestamp($timestamp);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('user_home', array('id' => $id));
        }


        return $this->render('homeUser.html', array('form' => $form->createView(), 'chatrooms' => $chatrooms, "user" => $user));


    }

    /**
     * @Route("user/id={userid}/chatroom/id={id}/register", name="register_chatroom")
     */
    public function registerInChatroomAction(Request $request, $userid, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($userid);

        $user->setChatroomId($id);
        $entityManager->flush();

        return $this->redirectToRoute('get_chatroom', array('userid' => $userid, "id" => $id));
    }

    /**
     * @Route("user/id={userid}/chatroom/id={id}", name="get_chatroom")
     */
    public function showChatroomAction(Request $request, $userid, $id)
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findByChatroomId($id);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userid);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT m
            FROM AppBundle:Message m
            WHERE m.chatroomId = :chatroomId
            OR m.type = :system'
        )->setParameter('chatroomId', $id)->setParameter('system', 1);


        $messages = $query->getResult();

        $message = new Message();
        $form = $this->createFormBuilder($message)
            ->add('context_text', TextareaType::class, array('label' => " "))
            ->add('save', SubmitType::class, array('label' => 'Send message to this chatroom'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $timestamp = new \DateTime("now");

            $message = $form->getData();
            $message->setSenderUser($userid);
            $message->setType(0);
            $message->setChatroomId($id);
            $message->setTimestamp($timestamp);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('get_chatroom', array('userid' => $userid, "id" => $id));
        }


        return $this->render('chatroom.html', array('form' => $form->createView(), 'messages' => $messages, 'users' => $users, 'userId' => $userid, "chatroom_id" => $id, "usr" => $user));


    }


    /**
     * @Route("user/id={userid}/createchatroom", name="chatroom")
     */
    public function createChatroomAction(Request $request, $userid)
    {

        $chatroom = new Chatroom();

        $form = $this->createFormBuilder($chatroom)
            ->add('name', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create chatroom'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $chatroom = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($chatroom);
            $entityManager->flush();

            return $this->redirectToRoute('get_chatroom', array('userid' => $userid, "id" => $chatroom->getId()));
        }

        return $this->render('chatroom_form.html', array(
            'form' => $form->createView(),
        ));

    }


    /**
     * @Route("/createuser", name="createUser")
     */
    public function createUserAction(Request $request)
    {

        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class)
            ->add("role", ChoiceType::class, array(
                'label' => 'Role',
                'choices' => array(
                    'Regular' => 0,
                    'Admin' => 1,
                )))
            ->add('save', SubmitType::class, array('label' => 'Create user'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

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
