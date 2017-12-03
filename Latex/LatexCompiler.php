<?php

namespace Statamic\Addons\Latex;


use Exception;
use Illuminate\Support\Facades\Storage;
use Statamic\Extend\Extensible;

class LatexCompiler
{


    use Extensible;

    /*
     * @var String 
     * Relative to Storage root;
     */
    protected $temporaryDirectory;


    public function latexToPng($container)
    {

        if ($container->already_exists) {
            return $container->image_tag;
        }

        try {
            $this->createLatexFile($container);
            $this->compileLatex($container);
            $this->dviToPs();
            $this->psToPng($container);
            Storage::copy($this->tempFile('png'), $container->url);
        } catch (Exception $exception) {
            $this->deleteTemporaryDirectory(); 
            return $exception->getMessage(); 
        }

        $this->deleteTemporaryDirectory();

        return $container->image_tag;
    }

    protected function compileLatex($container)
    {

        $command = $this->getConfig('path_to_latex') . ' --interaction=nonstopmode ';
        $command .= $this->path($this->tempFile('tex'));

        chdir($this->path($this->temporaryDirectory));
        exec($command);
        if (!Storage::exists($this->tempFile('dvi'))) {
            $log = Storage::get($this->tempFile('log')); #The log always exists, but now it's actually interesting since it'll contain an error
            throw new Exception('[latex error, code follows]<pre>' . $container->latex . '</pre><p><b>Log file:</b><pre>' . $log . '</pre></p> ');
        }
    }

    protected function dviToPs()
    {
        # DVI -> PostScript.   Since dvips uses lpr, which may be configured to actually print by default, force writing to a file with -o
        $command = $this->getConfig('path_to_dvips') . ' ' . $this->path($this->tempFile('dvi'));
        $command .= ' -o ' . $this->path($this->tempFile('ps'));
        exec($command);
        if (!Storage::exists($this->tempFile('ps'))) {
            throw new Exception('[dvi2ps error]');
        }
    }

    protected function psToPng($container)
    {
        # PostScript -> image.  Also trim based on corner pixel and set transparent color.
        $command = $this->getConfig('path_to_convert');
        $command .= ' -colorspace RGB -density ' . $container->resolution;
        $command .= ' -trim +page ' . $this->path($this->tempFile('ps')) . ' ' . $this->path($this->tempFile('png'));

        exec($command);
        if (!Storage::exists($this->tempFile('png'))) {
            return '[image convert error] ';
        }
    }


    protected function createLatexFile($container)
    {
        Storage::put($this->tempFile('tex'), $container->latex);
    }


    protected function createTemporaryDirectory()
    {
        $temporaryDirectory = 'local/temp/latex/' . uniqid();
        Storage::makeDirectory($temporaryDirectory);
        $this->temporaryDirectory = $temporaryDirectory . '/';
        return $this->temporaryDirectory;
    }

    protected function tempFile($extension)
    {

        if (!$this->temporaryDirectory) {
            $this->createTemporaryDirectory();
        }
        return $this->temporaryDirectory . 'input.' . $extension;
    }

    protected function deleteTemporaryDirectory()
    {
        Storage::deleteDirectory($this->temporaryDirectory);
    }

    protected function path($relativePath)
    {
        return Storage::getAdapter()->getPathPrefix() . $relativePath;
    }


}