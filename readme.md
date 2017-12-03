# Latex
Write Latex code in your template and get it rendered to images (PNG)

# Prerequisites
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class.
  Most are standard.   You may need relatively recent versions of some.


# Install
1. Copy files to `addons` folder
2. Specify the absolute paths to your latex, dvips and convert binaries in the settings or cp

# Usage
Wrap your LaTeX-Code within the latex-tags like so 
  
    {{ latex }}
        $ x = \sum_{k=1}^n k^2 $
    {{ /latex }}

# Options
You can specify a documentclass or the path to a custom header file.

    {{ latex documentclass="book" header="myheader.tex"}}
        $ x = \sum_{k=1}^n k^2 $
    {{ /latex }}

This is especially useful if you have custom header files and documentclasses in your local texmf-tree.  

# License
This project is licensed under the GNU License - see the [LICENSE.md](LICENSE.md) file for details


# Caveats
- Won't work on safe-mode PHP  (common enough on cheaper shared hosting)
- Fails on TeX that is more than one page.
  Should not bother you for most things that are inline.
  Workaround: use \small or \footnotesize and a larger DPI setting.
  TODO: think about better fixes.
- Image conversion can fail for very large images  (hence the DPI cap)
- I cannot guarantee this is safe from a security standpoint -- in theory it's mostly fine, but TeX *is* a full-fledged language.

# Acknowledgments

* This project is based on and inspired by [phplatex](https://github.com/scarfboy/phplatex) by [scarfboy](https://github.com/scarfboy).
