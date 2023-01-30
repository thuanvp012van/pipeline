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
                return call_user_func_array($this->destination, $params);
            }
        };
    }

    /**
     * Set the object being sent through the pipeline.
     *
     * @param mixed ...$params
     * @return static
     */
    public function send(mixed ...$params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param object ...$pipes
     * 
     * @return static
     */
    public function through(object ...$pipes): static
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * Push additional pipes onto the pipeline.
     *
     * @param object ...$pipes
     * 
     * @return static
     */
    public function pipe(object ...$pipes): static
    {
        $this->pipes = [...$this->pipes, ...$pipes];
        return $this;
    }

    /**
     * Set the method to call on the pipes.
     *
     * @param string $method
     * 
     * @return static
     */
    public function via(string $method): static
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
        return call_user_func_array($this->next, $this->params);
    }
}