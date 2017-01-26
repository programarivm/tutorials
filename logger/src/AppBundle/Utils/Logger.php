<?php
namespace AppBundle\Utils;

use AppBundle\Patterns\Singleton;

/**
 * Multipurpose logger class.
 *
 * Logs data into the given entity.
 */
class Logger extends Singleton
{
    // HTTP status codes

    const HTTP_OK = '200';
    const HTTP_REDIRECT = '301';
    const HTTP_NOT_FOUND = '404';
    const HTTP_ERROR = '500';

    // Debug mode

    const MODE_DEBUG = 'DEBUG';
    const MODE_INFO = 'INFO';
    const MODE_WARN = 'WARN';
    const MODE_ERROR = 'ERROR';

    /**
     * Entity manager
     * @var Doctrine\ORM\EntityManage
     */
    protected $em;

    /**
     * Entity object
     * @var Entity
     */
    protected $entity;

    /**
     * Initializes the logger.
     *
     * @param  Doctrine\ORM\EntityManager $em
     * @param  Entity $entity
     * @return AppBundle\Utils\Logger
     */
    public function init($em, $entity)
    {
        $this->em = $em;
        $this->entity = $entity;
        return $this;
    }

    /**
     * Writes the data into the underlaying log table.
     *
     * @param  array $data
     * @return AppBundle\Utils\Logger
     */
    public function write($data)
    {
      foreach($data as $key => $value)
      {
        $method = 'set' . $this->camelize($key);
        $this->entity->$method($value);
      }
      $this->em->persist($this->entity);
      $this->em->flush();
      $this->em->clear();
      return $this;
    }

    /* public function getByMode($mode)
    {

    } */

    /**
     * Converts foo_bar strings into fooBar
     *
     * @param  string $input
     * @param  string $separator
     * @return string
     */
    private function camelize($input, $separator = '_')
    {
      return str_replace($separator, '', ucwords($input, $separator));
    }
}
