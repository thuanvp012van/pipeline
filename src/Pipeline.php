<?php

namespace Penguin\Component\Pipeline;

use Closure;

class Pipeline
{
    protected array $params;

    protected array $pipes;

    protected Closure $next;
    
    protected Closure $destination;

    protected int $currentStack;

    protected string $method = 'process';

    public function __construct()
    {
        $this->next = function (mixed ...$params) {
            $this->currentStack++;
            if (isset($this->pipes[$this->currentStack])) {
                $params[] = $this->next;
                return $this->pipes[$this->currentStack]->{$this->method}(...$params);
            } else {
                $destination = $this->destination;
                return $destination(...$params);
            }
        };
    }

    /**
     * Set the object being sent through the pipeline.
     *
     * @param mixed ...$params
     * @return self
     */
    public function send(mixed ...$params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param object ...$pipes
     * 
     * @return self
     */
    public function through(object ...$pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * Push additional pipes onto the pipeline.
     *
     * @param object ...$pipes
     * 
     * @return self
     */
    public function pipe(object ...$pipes): self
    {
        $this->pipes = [...$this->pipes, ...$pipes];
        return $this;
    }

    /**
     * Set the method to call on the pipes.
     *
     * @param string $method
     * 
     * @return self
     */
    public function via(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param \Closure $destination
     * 
     * @return mixed
     */
    public function then(Closure $destination): mixed
    {
        $this->currentStack = -1;
        $this->destination = $destination;
        $next = $this->next;
        return $next(...$this->params);
    }
}