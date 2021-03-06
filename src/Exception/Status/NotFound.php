<?php
namespace FMUP\Exception\Status;

/**
 * Class NotFound - Exception to explain to framework that this page cannot be handled due to invalid resource
 * @package FMUP\Exception
 */
class NotFound extends \FMUP\Exception\Status
{
    /**
     * Must return understandable status
     * @see FMUP\Response\Header
     * @return string
     */
    public function getStatus()
    {
        return \FMUP\Response\Header\Status::VALUE_NOT_FOUND;
    }
}
