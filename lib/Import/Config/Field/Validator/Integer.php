<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Integer implements Validator
{
    private $empty;

    public function __construct($empty = false)
    {
        $this->setCanEmpty($empty);
    }

    public function setCanEmpty($empty = false)
    {
        $this->empty = (bool)$empty;
        return $this;
    }

    public function getCanEmpty()
    {
        return (bool)$this->empty;
    }

    public function validate($value)
    {
        return (bool) ($this->getCanEmpty() && $value == '') || \Is::integer($value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est un nombre entier";
    }
}
