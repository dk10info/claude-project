<?php

namespace Illuminate\Testing\Fluent\Concerns;

use Illuminate\Support\Traits\Dumpable;

trait Debugging
{
    use Dumpable;

    /**
     * Dumps the given props.
     *
     * @return $this
     */
    public function dump(?string $prop = null): self
    {
        dump($this->prop($prop));

        return $this;
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
     *
     * @return mixed
     */
    abstract protected function prop(?string $key = null);
}
