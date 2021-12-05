<?php


namespace RobotismPhp\Command\Parsing;


class CommandSignature
{
    public string $name;
    public ?string $description;
    /**
     * @var Parameter[]
     */
    public array $arguments=[];

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param array $arguments
     * @return self
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }
}