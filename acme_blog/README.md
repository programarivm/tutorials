Validating Doctrine Entities in the Model Layer with PHP Traits
==============================================================

* Author: Jordi Bassagañas, [programarivm.com](http://programarivm.com)

It's not uncommon to find dozens of Symfony examples out there showing how to validate Doctrine entities from within an MVC controller -- for documentation and learning purposes, I guess.

However, shouldn't that logic be moved to the model layer since its very nature is business logic? And what about the skinny controller/model pattern?

I think it should!

So, I suggest you stop writing the same validation code here and there in your controllers. Today I am guiding you through the process of creating a PHP trait for your Doctrine entities to validate themselves in a self-contained manner.

This way the validation code is only written once -- in the PHP trait.

## How does this work?

Here is how the whole thing looks from a functional perspective.

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
        $post->setContent('Lorem ipsum dolor sit amet.');
        $post->setCreatedAt(new \DateTime("now"));

        $comment = new BlogComment();

        $comment->setContent('This is a comment');
        $comment->setPost($post);
        $comment->setAuthor('Foobar');
        $comment->setUrl('http://www.foobarweb.com');
        $comment->setEmail('foobar@foobar.com');
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

Note that we're running this example in the `indexAction` of the `DefaultController`. In a nutshell, we're injecting Symfony's built-in [validator service](http://symfony.com/doc/current/components/validator.html) into our custom trait's `validate` method.

    $errors = array_merge (
      (array) $post->validate($this->get('validator')),
      (array) $comment->validate($this->get('validator'))
    );

The `validate` method returns an array of error messages, otherwise if everything goes OK it's `null`. Therefore, since the code above meets all validation rules, we'll get this message: `The entities were validated and persisted!` Nevertheless, if the input is changed for whatever reason as it is shown below:

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

Then our `indexAction` will print the following output:

`The post content must not be empty. This comment needs to have an associated post. The email "foobar" is not a valid email. The url "foobar" is not a valid url.`

# Step-by-step tutorial to the rescue!

Are you curious about how I've built this sample app? OK, now I am showing you how to build it from scratch.

## 1. Create a new Symfony project

To get started, the first thing to do is create a new Symfony project:

    composer create-project symfony/framework-standard-edition acme_blog

## 2. Set a MySQL database

Now create the `docs/database.sql` file:

    DROP DATABASE IF EXISTS acme_blog;

    CREATE DATABASE acme_blog;

    USE acme_blog;

    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES
    ON acme_blog.* TO 'acme_blog_user'@'localhost' IDENTIFIED BY 'password';

    CREATE TABLE `blog_post` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
      `content` longtext COLLATE utf8_unicode_ci NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `blog_comment` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `post_id` bigint(20) NOT NULL,
      `author` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
      `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
      `url` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
      `content` longtext COLLATE utf8_unicode_ci NOT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `blog_comment_post_id_idx` (`post_id`),
      CONSTRAINT `blog_post_id` FOREIGN KEY (`post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

And run the script on your MySQL console, phpMyAdmin, MySQL WorkBench, or however you like. That's it! Once the database is running on your server, make sure the connection is properly set by updating the `app\config\parameters.yml` file:

    # This file is auto-generated during the composer install
    parameters:
        database_host: 127.0.0.1
        database_port: null
        database_name: acme_blog
        database_user: acme_blog_user
        database_password: password
        mailer_transport: smtp
        mailer_host: 127.0.0.1
        mailer_user: null
        mailer_password: null
        secret: ThisTokenIsNotSoSecretChangeIt

## 3. Create the Doctrine entities

At this point we're creating our app's Doctrine entities by using reverse engineering.

On the one hand run this command:

    php bin/console doctrine:mapping:import --force AppBundle yml

Voila! A couple of meatadata files will be generated:

- `src\AppBundle\Resources\config\doctrine\BlogPost.orm.yml`
- `src\AppBundle\Resources\config\doctrine\BlogComment.orm.yml`

On the other hand, build the entity classes by just running these two commands:

    php bin/console doctrine:mapping:convert annotation ./src
    php bin/console doctrine:generate:entities AppBundle

That's magic, so you may wish to check [Symfony's official documentation](http://symfony.com/doc/current/doctrine/reverse_engineering.html) for additional information on how to generate entities from an existing database.

## 4. Configure a virtual host

As you see I'm configuring Apache in this tutorial but you can use the web server you like the most.

    <VirtualHost *:80>
      ServerAdmin webmaster@dummy-host.example.com
      DocumentRoot "c:/wamp/www/acme_blog/web"
      ServerName acme-blog.local
      ErrorLog "logs/acme-blog.log"
      CustomLog "logs/acme-blog.local-access.log" common
    </VirtualHost>

By the way, don't forget to add a new entry in your `hosts` file:

    127.0.0.1		acme-blog.local

## 5. Define the validation rules of your app's domain

Adding validation rules to the entities according to the app's domain specification is an important part of the setup process in my opinion -- so take your time to figure out how to persist consistent data into your database.

Here's a basic example:

    # src/AppBundle/Resources/config/validation.yml
    AppBundle\Entity\BlogPost:
        properties:
            title:
                - NotBlank:
                    message: The post title is required.
            content:
                - NotBlank:
                    message: The post content must not be empty.
            createdAt:
                - NotBlank:
                    message: The post's creation date must be set.

    AppBundle\Entity\BlogComment:
        properties:
            post:
                - NotNull:
                    message: This comment needs to have an associated post.
            author:
                - NotBlank:
                    message: The author field must not be blank.
            email:
                - NotBlank: ~
                - Email:
                    message: The email {{ value }} is not a valid email.
            url:
                - NotBlank: ~
                - Url:
                    message: The url {{ value }} is not a valid url.

Note how they are written in the `src/AppBundle/Resources/config/validation.yml` file. Please visit [Symfony's official documentation](https://symfony.com/doc/current/reference/constraints.html) for further information on how to define validation constraints.

## 6. Write the validation logic in a PHP trait

As mentioned earlier, Symfony's validator component is injected into the `validate` method of the `AcmeEntity` trait.

    // src/AppBundle/Entity/AcmeEntity.php
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

Once the `AcmeEntity` trait is ready to be used, just add one single line of code to both the `BlogPost` and `BlogComment` entities:

    use AcmeEntity;

## Conclusion

Congratulations! You've learned how to move Doctrine entities' validation logic from the controller layer to the business layer. Specifically, we've written it into a PHP trait in order to benefit from code reuse as well as make our app's models and controllers skinny. We've injected Symfony's validation service into the trait's `validate` method.
