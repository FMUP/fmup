<?php
namespace FMUP;

/**
 * Class ErrorHandler
 * @package FMUP
 */
class ErrorHandler
{
    private $handlers = array();
    private $response;
    private $request;
    private $bootstrap;

    const WAY_APPEND = 'WAY_APPEND';
    const WAY_PREPEND = 'WAY_PREPEND';

    /**
     * @param ErrorHandler\Plugin\Abstraction $handler
     * @param string $way
     * @return $this
     */
    public function add(ErrorHandler\Plugin\Abstraction $handler, $way = self::WAY_APPEND)
    {
        if ($way == self::WAY_PREPEND) {
            array_unshift($this->handlers, $handler);
        } else {
            array_push($this->handlers, $handler);
        }
        return $this;
    }

    /**
     * @return ErrorHandler\Plugin\Abstraction[]
     */
    public function get()
    {
        return $this->handlers;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->handlers = array();
        return $this;
    }

    /**
     * @param \Exception $e
     * @return $this
     * @throws Exception
     * @throws \Exception
     */
    public function handle(\Exception $e)
    {
        $this->init();
        if (!count($this->get())) {
            throw $e;
        }
        foreach ($this->get() as $handler) {
            $handler->setResponse($this->getResponse())
                ->setRequest($this->getRequest())
                ->setBootstrap($this->getBootstrap())
                ->setException($e);
            if ($handler->canHandle()) {
                $handler->handle();
            }
        }
        return $this;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function getResponse()
    {
        if (!$this->response) {
            throw new Exception('Unable to access response. Not set');
        }
        return $this->response;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new Exception('Unable to access request. Not set');
        }
        return $this->request;
    }

    /**
     * @param Bootstrap $bootstrap
     * @return $this
     */
    public function setBootstrap(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    /**
     * @return Bootstrap
     * @throws Exception
     */
    public function getBootstrap()
    {
        if (!$this->bootstrap) {
            throw new Exception('Unable to access bootstrap. Not set');
        }
        return $this->bootstrap;
    }

    /**
     * Optional way to init some plugins
     * @return $this
     */
    public function init()
    {
        return $this;
    }
}
