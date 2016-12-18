<?php

namespace AppBundle\Entity;

trait AcmeEntity
{
  /**
   * Validates an entity using the rules definied in validation.yml
   *
   * @param   Symfony\Component\Validator\Validation $service
   * @return  null|array An array of error messages, otherwise null.
   */
  public function validate($service)
  {
    $errors = $service->validate($this);

    foreach($errors as $error)
    {
      $messages[] = $error->getMessage();
    }

    return $messages;
  }
}
