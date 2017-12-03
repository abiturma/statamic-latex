<?php

namespace Statamic\Addons\Latex;

use Statamic\Extend\Tags;

class LatexTags extends Tags
{
    /**
     * @var LatexCompiler
     */
    protected $compiler;

    /**
     * LatexTags constructor.
     * @param LatexCompiler $compiler
     */
    public function __construct(LatexCompiler $compiler)
    {
        $this->compiler = $compiler;
    }
    
    /**
     * The {{ latex }} tag
     *
     * @return string|array
     */
    public function index()
    {
        $container = new InputContainer($this->content,$this->getParam('res'),$this->getParam('documentclass'),$this->getParam('header'));
        
        return $this->compiler->latexToPng($container);
    }

    /**
     * The {{ latex:example }} tag
     *
     * @return string|array
     */
    public function example()
    {
        //
        return 'Try with $x = \sum_{i=1}^n x^2'; 
        
    }
}
