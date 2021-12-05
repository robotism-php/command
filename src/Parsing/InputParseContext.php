<?php


namespace RobotismPhp\Command\Parsing;


class InputParseContext
{
    const PHASE_COMMAND_NAME=0;
    const PHASE_COMMAND_PARAMETER=1;
    protected int $phase=InputParseContext::PHASE_COMMAND_NAME;

    /**
     * @return int
     */
    public function getPhase(): int
    {
        return $this->phase;
    }

    /**
     * @param int $phase
     */
    public function setPhase(int $phase): void
    {
        $this->phase = $phase;
    }

}