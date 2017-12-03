<?php

namespace Statamic\Addons\Latex;


use Illuminate\Support\Facades\Storage;
use Statamic\Extend\Extensible;


class InputContainer
{

    protected $documentclass; 
    
    protected $header; 
    
    protected $resolution; 
    
    protected $inputString;

    use Extensible {
        __get as public parentget;
    }

    public function __construct($inputString, $resolution, $documentclass, $header)
    {
        $this->inputString = $inputString;
        $this->resolution = $resolution ? max(10,min($resolution,300)) : 90;
        $this->documentclass = $documentclass ?: $this->getConfig('documentclass');
        $this->header = $header ? "\\include{" . $header . "}" : '';
    }


    public function getHashAttribute()
    {
        return sha1($this->latex. '#' . $this->resolution); 
    }

    public function getLatexAttribute()
    {
        
        return "\\documentclass[14pt,landscape]{". $this->documentclass."}\n" .
            "\\usepackage{color}\n" .
            "\\usepackage{amsmath}\n\\usepackage{amsfonts}\n\\usepackage{amssymb}\n" .
            "\\pagestyle{empty}\n" .  #removes header/footer; necessary for trim
            $this->header . 
            "\\begin{document}\n" .
            $this->inputString . "\n" .
            "\\end{document}\n";
    }

    public function getAltAttribute()
    {
        return htmlspecialchars(preg_replace('/[\$\&\n]/', '', $this->inputString));
    }

    public function getVerticalAlignmentAttribute()
    {
        # Experiment: Tries to adjust vertical positioning, so that rendered TeX text looks natural enough inline with HTML text
        #  Only descenders are really a problem since HTML's leeway is upwards.
        #  TODO: This can always use more work. 
        #        Avoid using characters that are part of TeX commands.
        #  Some things vary per font, e.g. the slash. In the default CM it is a descender, in Times and others it isn't.
        $ascenders = "/(b|d|f|h|i|j|k|l|t|A|B|C|D|E|F|G|H|I|J|L|K|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|\[|\]|\\{|\\}|\(|\)|\/|0|1|2|3|4|5|6|7|8|9|\\#|\*|\?|'|\\\\'|\\\\`|\\\\v)/";
        $monoliners = "/(a|c|e|m|n|o|r|s|u|v|w|x|z|-|=|\+|:|.)/";
        $descenders = "/(g|j|p|\/|q|y|Q|,|;|\[|\]|\\{|\\}|\(|\)|\#|\\\\LaTeX|\\\\TeX|\\\\c\{)/";
        $deepdescenders = "/(\[|\]|\\{|\\}|\(|\)|\\int)/";

        $ba = preg_match_all($ascenders, $this->inputString, $m);
        $bm = preg_match_all($monoliners, $this->inputString, $m);
        $bd = preg_match_all($descenders, $this->inputString, $m);
        $dd = preg_match_all($deepdescenders, $this->inputString, $m);
        if ($dd > 0) $verticalalign = "vertical-align: -25%";   # deep descenders: move down
        else if ($bd > 0 && $ba == 0) $verticalalign = "vertical-align: -15%";   # descenders:  move down
        else if ($bd == 0 && $ba > 0) $verticalalign = "vertical-align: 0%";     # ascenders only: move up/do nothing?
        else if ($bd == 0 && $ba == 0) $verticalalign = "vertical-align: 0%";     # neither    vertical-align: 0%
        else                       $verticalalign = "vertical-align: -15%";   # both ascender and regular descender

        return $verticalalign;
    }

    public function getAlreadyExistsAttribute()
    {
        return Storage::exists($this->url); 
    }

    public function getUrlAttribute()
    {
        return '/assets/' . $this->getConfig('asset_folder') . '/' . $this->hash . '.png';
    }

    public function getImageTagAttribute()
    {
        $alt = $this->alt; 
        return '<img style="' . $this->vertical_alignment .
            '" title="' . $alt . '" alt="'
            . $alt . '" src=' . $this->url . ' >';
    }

    public function getResolutionAttribute()
    {
        return $this->resolution; 
    }
    
    
    
    public function __get($name)
    {
        $getter = camel_case("get{$name}Attribute"); 
        if(method_exists($this,$getter)) {
            return $this->$getter();
        }
        return $this->parentget($name); 
    }
    
    


}