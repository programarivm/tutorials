<?php

/*

Developing AclManager TODO tasks:

1. Ceck what returns each method

2. The repositories are hardcoded:

    $repository = $this->em->getRepository('AppBundle:AclEntry');

3. Manage excepctions, look at https://github.com/myclabs/ACL/blob/master/src/Doctrine/ACLQueryHelper.php



 */

namespace Tests\Programarivm\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Programarivm\EasyACL\AclManager;
use Programarivm\Entity\AclRole;
use Programarivm\Entity\AclResource;
use Programarivm\Entity\AclEntry;

class AclTest extends WebTestCase
{
  protected static $em;

  protected static $aclMngr;

  public static function setUpBeforeClass()
  {
    self::bootKernel();

    self::$em = static::$kernel->getContainer()
      ->get('doctrine')
      ->getManager();

    // $sql = file_get_contents(static::$kernel->getRootDir() . '/../docs/database.sql');
    // $stmt = self::$em->getConnection()->prepare($sql);
    // $foo = $stmt->execute();

    self::$aclMngr = AclManager::getInstance()->init(self::$em);
  }

  protected function setUp()
  {

  }

  public function testAddRoleInvalidArgumentException()
  {
    $role = self::$aclMngr->addRole([
      'foo' => 'Foo property',
      'slug' => 'Foo slug',
      'description' => 'Foo description'
    ]);
  }

  public function testAddRole()
  {
    $nRoles = self::$em->getRepository('AppBundle:AclRole')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $role = self::$aclMngr->addRole([
      'name' => 'Admin',
      'slug' => 'admin',
      'description' => 'The admin of the website'
    ]);

    $mRoles = self::$em->getRepository('AppBundle:AclRole')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $this->assertTrue(is_int($role->getId()));
    $this->assertEquals($nRoles + 1, $mRoles);
  }

  public function testRemoveRole()
  {
    $nRoles = self::$em->getRepository('AppBundle:AclRole')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $role = self::$aclMngr->addRole([
      'name' => 'Admin',
      'slug' => 'admin',
      'description' => 'The admin of the website'
    ]);

    self::$aclMngr->removeRole($role);

    $mRoles = self::$em->getRepository('AppBundle:AclRole')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $this->assertEquals($nRoles, $mRoles);
  }

  public function testAddResource()
  {
    $nResources = self::$em->getRepository('AppBundle:AclResource')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $resource = self::$aclMngr->addResource([
      'name' => 'Login',
      'slug' => 'login',
      'description' => 'URL to log in the site',
      'type' => AclManager::RESOURCE_URL
    ]);

    $mResources = self::$em->getRepository('AppBundle:AclResource')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $this->assertTrue(is_int($resource->getId()));
    $this->assertEquals($nResources + 1, $mResources);

  }

  public function testGrant()
  {
    $nEntries = self::$em->getRepository('AppBundle:AclEntry')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $role = self::$aclMngr->addRole([
      'name' => 'Editor',
      'slug' => 'editor',
      'description' => 'Can edit content'
    ]);

    $resource = self::$aclMngr->addResource([
      'name' => 'Edit post',
      'slug' => 'post/edit/{id}',
      'description' => 'Edits the given post',
      'type' => AclManager::RESOURCE_URL
    ]);

    self::$aclMngr->grant($role, $resource);

    $mEntries = self::$em->getRepository('AppBundle:AclEntry')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $this->assertEquals($nEntries + 1, $mEntries);
  }

  public function testIsGranted()
  {
    $role = self::$aclMngr->addRole([
      'name' => 'Editor',
      'slug' => 'editor',
      'description' => 'Can edit content'
    ]);

    $resource = self::$aclMngr->addResource([
      'name' => 'Edit post',
      'slug' => 'post/edit/{id}',
      'description' => 'Edits the given post',
      'type' => AclManager::RESOURCE_URL
    ]);

    self::$aclMngr->grant($role,$resource);

    $isGranted = self::$aclMngr->isGranted($role, $resource);

    $this->assertTrue($isGranted);
  }

  public function testRevoke()
  {
    $nEntries = self::$em->getRepository('AppBundle:AclEntry')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $role = self::$aclMngr->addRole([
      'name' => 'Editor',
      'slug' => 'editor',
      'description' => 'Can edit content'
    ]);

    $resource = self::$aclMngr->addResource([
      'name' => 'Edit post',
      'slug' => 'post/edit/{id}',
      'description' => 'Edits the given post',
      'type' => AclManager::RESOURCE_URL
    ]);

    self::$aclMngr->grant($role,$resource)->revoke($role,$resource);

    $mEntries = self::$em->getRepository('AppBundle:AclEntry')
      ->createQueryBuilder('ar')
      ->select('count(ar.id)')
      ->getQuery()
      ->getSingleScalarResult();

    $this->assertEquals($nEntries, $mEntries);
  }

}
