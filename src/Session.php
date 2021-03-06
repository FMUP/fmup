<?php
namespace FMUP;

/**
 * Class Session
 * @package FMUP
 */
class Session
{
    use Sapi\OptionalTrait;

    private $sessionState;
    private $name;
    private $id;
    private static $instance;

    private function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * @param bool $deleteOldSession
     * @return bool
     */
    public function regenerate($deleteOldSession = false)
    {
        $success = false;
        if ($this->isStarted()) {
            $success = $this->sessionRegenerateId((bool)$deleteOldSession);
        }
        return $success;
    }

    /**
     * @param bool|false $deleteOldSession
     * @return bool
     * @codeCoverageIgnore
     */
    protected function sessionRegenerateId($deleteOldSession = false)
    {
        return session_regenerate_id((bool)$deleteOldSession);
    }

    /**
     * Retrieve session system - start session if not started
     * @return Session
     */
    final public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Define session name
     * @param string $name
     * @throws \FMUP\Exception if session name defined contain only numbers
     * @return $this
     */
    public function setName($name)
    {
        if (!$this->isStarted()) {
            if (is_numeric($name)) {
                throw new Exception('Session name could not contain only numbers');
            }
            $this->name = (string)$name;
        }
        return $this;
    }

    /**
     * Retrieve session name
     * @return string|null
     */
    public function getName()
    {
        if ($this->isStarted() && is_null($this->name)) {
            $this->name = $this->sessionName();
        }
        return $this->name;
    }

    /**
     * @param string $name
     * @return string
     * @codeCoverageIgnore
     */
    protected function sessionName($name = null)
    {
        return session_name($name);
    }

    /**
     * Define session id
     * @param string $id
     * @return $this
     * @throws Exception
     */
    public function setId($id)
    {
        if (!$this->isStarted()) {
            if (!preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id)) {
                throw new Exception('Session name is not valid');
            }
            $this->id = (string)$id;
        }
        return $this;
    }

    /**
     * @param string|null $sessionId
     * @return string
     * @codeCoverageIgnore
     */
    protected function sessionId($sessionId = null)
    {
        return session_id($sessionId);
    }

    /**
     * Retrieve session name
     * @return string|null
     */
    public function getId()
    {
        if ($this->isStarted() && is_null($this->id)) {
            $this->id = $this->sessionId();
        }
        return $this->id;
    }

    /**
     * Check whether session is started
     * @return bool
     */
    public function isStarted()
    {
        if (is_null($this->sessionState)) {
            $this->sessionState = version_compare($this->phpVersion(), '5.4.0', '>=')
                ? $this->sessionStatus() === PHP_SESSION_ACTIVE
                : $this->sessionId() !== '';
        }
        return $this->sessionState;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function phpVersion()
    {
        return phpversion();
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    protected function sessionStatus()
    {
        return session_status();
    }

    /**
     * Start session if not started and return if session is started
     * @return bool
     */
    public function start()
    {
        if (!$this->isStarted() && $this->getSapi()->get() != Sapi::CLI) {
            if ($this->getId()) {
                $this->sessionId($this->getId());
            }
            if ($this->getName()) {
                $this->sessionName($this->getName());
            }
            $this->sessionState = $this->sessionStart();
        }

        return (bool)$this->sessionState;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    protected function sessionStart()
    {
        return session_start();
    }

    /**
     * Retrieve all session values defined
     * @return array
     */
    public function getAll()
    {
        return $this->start() ? $_SESSION : array();
    }

    /**
     * Define all session values
     * @param array $values
     * @return $this
     */
    public function setAll(array $values = array())
    {
        if ($this->start()) {
            $_SESSION = $values;
        }
        return $this;
    }

    /**
     * Retrieve a session value
     * @param string $name
     * @return mixed
     * @codeCoverageIgnore
     */
    public function get($name)
    {
        $name = (string) $name;
        return $this->has($name) ? $_SESSION[$name] : null;
    }

    /**
     * Check whether a specific information exists in session
     * @param string $name
     * @return bool
     * @codeCoverageIgnore
     */
    public function has($name)
    {
        $name = (string) $name;
        return $this->start() && array_key_exists($name, $_SESSION);
    }

    /**
     * Define a specific value in session
     * @param string $name
     * @param mixed $value
     * @return $this
     * @codeCoverageIgnore
     */
    public function set($name, $value)
    {
        if ($this->start()) {
            $name = (string) $name;
            $_SESSION[$name] = $value;
        }
        return $this;
    }

    /**
     * Forget all values in session without destructing it
     * @return $this
     * @codeCoverageIgnore
     */
    public function clear()
    {
        if ($this->start()) {
            $_SESSION = array();
        }
        return $this;
    }

    /**
     * Delete a specific information from session
     * @param string $name
     * @return $this
     * @codeCoverageIgnore
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($_SESSION[$name]);
        }
        return $this;
    }

    /**
     * Destroy current session
     * @return bool success or failure on session destruction
     * @codeCoverageIgnore
     */
    public function destroy()
    {
        if ($this->start()) {
            $this->sessionState = !session_destroy();
            unset($_SESSION);

            return !$this->sessionState;
        }

        return false;
    }
}
