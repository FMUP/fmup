<?php
namespace FMUP\Exception\Status;

/**
 * Class Forbidden - Exception to explain to framework that this page cannot be handled due to rights
 * @package FMUP\Exception
 */
class Forbidden extends \FMUP\Exception\Status
{
    /**
     * Must return understandable status
     * @see FMUP\Response\Header
     * @return string
     */
    public function getStatus()
    {
        return \FMUP\Response\Header\Status::VALUE_FORBIDDEN;
    }
}
