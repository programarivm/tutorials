<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\BlogPost;
use AppBundle\Entity\BlogComment;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $post = new BlogPost();

        $post->setTitle('This is my first post');
        // $post->setContent('Lorem ipsum dolor sit amet.');
        $post->setCreatedAt(new \DateTime("now"));

        $comment = new BlogComment();

        $comment->setContent('This is a comment');
        // $comment->setPost($post);
        $comment->setAuthor('Foobar');
        $comment->setUrl('foobar');
        $comment->setEmail('foobar');
        $comment->setCreatedAt(new \DateTime("now"));

        // let's get a set of user-friendly errors
        $errors = array_merge (
          (array) $post->validate($this->get('validator')),
          (array) $comment->validate($this->get('validator'))
        );

        if(!empty($errors))
        {
          return new Response(implode(' ',$errors));
        }
        else
        {
          $em = $this->getDoctrine()->getManager();
          $em->persist($post);
          $em->persist($comment);
          $em->flush();
          return new Response('The entities were validated and persisted!');
        }
    }
}
