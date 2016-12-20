<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\ActionLog;
use AppBundle\Utils\Logger;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $logger = Logger::getInstance()->init(
          $this->getDoctrine()->getManager(),
          new ActionLog);

        $data01 = [
          'name' => 'foo',
          'description' => 'bar',
          'created_at' => new \DateTime("now")
        ];

        $data02 = [
          'name' => 'lorem',
          'description' => 'ipsum',
          'created_at' => new \DateTime("now")
        ];

        $logger->write($data01)->write($data02);

        return new Response('Thank you for logging data!');
    }
}
