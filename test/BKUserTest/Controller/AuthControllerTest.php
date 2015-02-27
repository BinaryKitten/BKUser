<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 26/02/2015
 * Time: 17:01
 */

namespace BKUserTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include 'config/application.config.php'
        );
        parent::setUp();
    }

    public function testLogintActionCanBeAccessed()
    {
        $options = $this->getApplicationServiceLocator()->get('bkuser\auth\config');
        $class = \BKUser\Controller\AuthController::class;
        $classParts = explode('\\', $class);
        $className = array_pop($classParts);

        $this->dispatch($options['routes']['login']);

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('BKUser');
        $this->assertControllerName($class);
        $this->assertControllerClass($className);
        $this->assertMatchedRouteName('bkuser-login');
    }

    public function testLogoutActionCanBeAccessed()
    {
        $options = $this->getApplicationServiceLocator()->get('bkuser\auth\config');
        $class = \BKUser\Controller\AuthController::class;
        $classParts = explode('\\', $class);
        $className = array_pop($classParts);

        $this->dispatch($options['routes']['logout']);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo($options['routes']['login']);
        $this->assertModuleName('BKUser');
        $this->assertControllerName($class);
        $this->assertControllerClass($className);
        $this->assertMatchedRouteName('bkuser-logout');
    }
}
