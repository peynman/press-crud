<?php

namespace Larapress\CRUD\Translation;

use Illuminate\Translation\FileLoader;
use Larapress\Core\Extend\Helpers;

class PackageFileLoader extends FileLoader
{
    /**
     * Load a local namespaced translation group for overrides.
     *
     * @param  array $lines
     * @param  string $locale
     * @param  string $group
     * @param  string $namespace
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";

        if ($this->files->exists($file)) {
            return Helpers::arrayMergeRecursive($lines, $this->files->getRequire($file));
        }

        return $lines;
    }

    /**
     * Load a namespaced translation group.
     *
     * @param  string $locale
     * @param  string $group
     * @param  string $namespace
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadNamespaced($locale, $group, $namespace)
    {
        if (isset($this->hints[$namespace])) {
            $lines = [];
            foreach ($this->hints[$namespace] as $path) {
                $lines = Helpers::arrayMergeRecursive($lines, $this->loadPath($path, $locale, $group));
            }

            return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
        }

        return [];
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        if (!isset($this->hints[$namespace])) {
            $this->hints[$namespace] = [];
        }
        $this->hints[$namespace][] = $hint;
    }
}