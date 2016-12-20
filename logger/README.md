# Wrapping Doctrine Entities into a Singletonized Logger
========================================================

* Author: Jordi Bassagañas, [programarivm.com](http://programarivm.com)

Have you ever needed to log information into a MySQL database via Doctrine entities? If so, keep reading. In today's tutorial we are building a very simple logger that is able to communicate with the underlying table of your choice. We'll be relying on two well-known software design patterns: Singleton and Dependency Injection.

## How does this work?

Here is how the whole thing looks from a functional perspective.

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

Note that we're running this example in the `indexAction` of the `DefaultController`. In a nutshell, we're getting a singleton instance of our logger. Then, we inject Doctrine's entity manager along with the entity of your choice.

    $logger = Logger::getInstance()->init(
      $this->getDoctrine()->getManager(),
      new ActionLog);

Writing data is a piece of cake! Just fill an array -- or arrays -- with the data to be inserted into the database:

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

And proceed as shown below:

    $logger->write($data01)->write($data02);
