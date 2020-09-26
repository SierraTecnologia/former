<?php namespace Former\Exceptions;

class InvalidFrameworkException extends \RuntimeException
{

    /**
     * reference to framework class
     *
     * @var string
     */
    private $framework;

    /**
     * Set framework
     *
     * @param  string $framework
     * @return $this
     */
    public function setFramework($framework)
    {
        $this->framework = $framework;
        $this->message = "Framework was not found [{$this->framework}]";

        return $this;
    }

}
