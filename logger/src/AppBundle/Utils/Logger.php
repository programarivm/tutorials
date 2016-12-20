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
