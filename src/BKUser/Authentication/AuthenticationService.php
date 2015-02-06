<?php

namespace BKUser\Authentication;

use Zend\Authentication\Adapter;
use Zend\Authentication\Exception;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Storage;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class AuthenticationService extends ZendAuthenticationService implements ServiceManagerAwareInterface
{

    /** @var ServiceManager $serviceManager */
    protected $serviceManager = null;

    /** @var array $config */
    protected $config = array();

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return AuthenticationService
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->config = $serviceManager->get('bkuser/auth/config');

        return $this;
    }

    /**
     * @param $identity
     * @param $credential
     * @throws Exception\RuntimeException if answering the authentication query is impossible
     * @return AuthenticationResult
     */
    public function authenticateCredentials($identity, $credential)
    {
        if (empty($identity)) {
            return new AuthenticationResult(AuthenticationResult::FAILURE, 'No Identity was specified');
        }
        return $this->authenticate(
            $this->getAdapter()
                ->setIdentity($identity)
                ->setCredential($credential)
        );
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(Adapter\AdapterInterface $adapter = null)
    {
        if (!$adapter) {
            if (!$adapter = $this->getAdapter()) {
                throw new Exception\RuntimeException('An adapter must be set or passed prior to calling authenticate()');
            }
        }
        $result = $adapter->authenticate();

        /**
         * ZF-7546 - prevent multiple successive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $data = (array)$this->getAdapter()->getResultRowObject(null, array('password'));
            $userEntity = $this->config['user-entity'];
            $userEntityHydrator = $this->config['user-entity-hydrator'];

            if ($this->serviceManager->has($userEntity)) {
                $userEntityInstance = $this->serviceManager->get($userEntity);
            } elseif(class_exists($userEntity)) {
                $userEntityInstance = new $userEntity;
            }

            if ($this->serviceManager->has($userEntityHydrator)) {
                $hydrator = $this->serviceManager->get($userEntityHydrator);
            } elseif(class_exists($userEntityHydrator)) {
                $hydrator = new $userEntityHydrator;
            }
            if ($hydrator) {
                $userEntityInstance = $hydrator->hydrate($data, $userEntityInstance);
            }

            $this->getStorage()->write($userEntityInstance);
        }

        return $result;
    }


} 