<?php

namespace Illuminate\View\Engines;

use Illuminate\Contracts\View\Engine;
use Illuminate\Filesystem\Filesystem;

class FileEngine implements Engine
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file engine instance.
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->files->get($path);
    }
}
