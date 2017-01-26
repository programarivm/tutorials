<?php
namespace Programarivm\EasyACL;

use Programarivm\Patterns\Singleton;

use Programarivm\Entity\AclRole;
use Programarivm\Entity\AclResource;
use Programarivm\Entity\AclEntry;

/**
 * ACL manager.
 */
class AclManager extends Singleton
{
  const RESOURCE_URL = 'url';
  const RESOURCE_FILE = 'file';

  /**
   * Entity manager.
   *
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;

  /**
   * ACL entity.
   *
   * @var Doctrine\ORM\Entity
   */
  protected $entity;

  /**
   * Database prefix.
   *
   * @var string
   */
  protected $dbPrefix = '';

  /**
   * Initializes the ACL manager.
   *
   * @param  Doctrine\ORM\EntityManager $em
   * @return AppBundle\Utils\Logger
   */
  public function init($em)
  {
      $this->em = $em;

      return $this;
  }

  public function setDbPrefix($prefix)
  {
    $this->dbPrefix .= $prefix . '_';
  }

  public function getDbPrefix()
  {
    return $this->dbPrefix;
  }

  public function addRole(array $data)
  {
    try
    {
      $this->setEntity(new AclRole, $data)->persistEntity();

      return $this->entity;
    }

    catch(\Exception $e)
    {
      error_log("Caught ACL exception, adding role. " . $e->getMessage(), 0);
    }

    return false;
  }

  public function removeRole(AclRole $role)
  {
    try
    {
      $this->em->remove($role);
      $this->em->flush();

      return true;
    }

    catch(\Exception $e)
    {
      error_log("Caught ACL exception, removing role. " . $e->getMessage(), 0);
    }

    return false;
  }

  public function addResource(array $data)
  {
    $this->setEntity(new AclResource, $data)->persistEntity();

    return $this->entity;
  }

  public function removeResource(AclResource $resource)
  {
    try
    {
      $this->em->remove($resource);
      $this->em->flush();

      return true;
    }

    catch(\Exception $e)
    {
      error_log("Caught ACL exception, removing resource. " . $e->getMessage(), 0);
    }

    return false;
  }

  public function grant(AclRole $role, AclResource $resource)
  {
    $data = [
      $this->getDbPrefix() . 'acl_role' => $role,
      $this->getDbPrefix() . 'acl_resource' => $resource
    ];

    $this->setEntity(new AclEntry, $data)->persistEntity();

    return $this;
  }

  public function revoke(AclRole $role, AclResource $resource)
  {
    try
    {
      $repository = $this->em->getRepository('AppBundle:AclEntry');

      $entry = $repository->findOneBy([
        'aclRole' => $role->getId(),
        'aclResource' => $resource->getId()
      ]);

      $this->em->remove($entry);
      $this->em->flush();

      return $this->entity;
    }

    catch(\Exception $e)
    {
      error_log("Caught ACL exception, revoking access. " . $e->getMessage(), 0);
    }

    return false;
  }

  public function isGranted(AclRole $role, AclResource $resource)
  {
    try
    {
      $repository = $this->em->getRepository('AppBundle:AclEntry');

      $entry = $repository->findOneBy([
        'aclRole' => $role->getId(),
        'aclResource' => $resource->getId()
      ]);

      return isset($entry);
    }

    catch(\Exception $e)
    {
      error_log("Caught ACL exception, granting access. " . $e->getMessage(), 0);
    }

    return false;
  }

  /**
   * Sets the underlying Doctrine entity.
   *
   * @param $entity
   * @param  array $data The entity's properties to be set
   * @return AclManager
   *
   * @throws \InvalidArgumentException The given properties are not set in $entity
   */
  protected function setEntity($entity, array $data)
  {
      $this->entity = $entity;

      foreach($data as $key => $value)
      {
        if (!property_exists($this->entity, lcfirst($this->camelize($key))))
        {
          throw new \InvalidArgumentException('The property ' . $key . ' does not exist in the ' . get_class($this->entity) . ' entity');
        }
        else
        {
          $method = 'set' . $this->camelize($key);
          $this->entity->$method($value);
        }
      }

      return $this;
  }

 /**
  * Converts foo_bar strings into FooBar strings.
  *
  * @param  string $input
  * @param  string $separator
  * @return string
  */
  protected function camelize($input, $separator = '_')
  {
    return str_replace($separator, '', ucwords($input, $separator));
  }

  /**
   * Persists the underlying Doctrine entity.
   *
   * @return boolean True if the entity is successfully persisted; otherwise false.
   */
  protected function persistEntity()
  {
   try
   {
     $this->em->persist($this->entity);
     $this->em->flush();

     return true;
   }

   catch(\Exception $e)
   {
     error_log("Caught ACL exception, persisting entity. " . $e->getMessage(), 0);
   }

   return false;
  }
}
