<?php

require_once ('text_utils.php');

class recipe_card_generator
{
    private $_pdf = null;

    public function generate_pdf ($recipes)
    {
        $this->_pdf = new Zend_Pdf();

        $i = 0;
        foreach ($recipes as $r)
        {
            $this->add_recipe ($r, $i);
            $i++;
        }

        return $this->_pdf;
    }


    private function add_recipe ($recipe, $idx)
    {
        if ($idx % 2 == 0)
        {
            $this->_front_page = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $this->_back_page  = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

            $this->_pdf->pages[] = $this->_front_page;
            $this->_pdf->pages[] = $this->_back_page;

            $this->_y_offset = 5 * 72;
        }
        else
        {
            $this->_y_offset = 0;
        }

        $this->add_borders ($this->_front_page, $idx);
        $this->add_borders ($this->_back_page, $idx);

        $this->add_text ($this->_front_page, $this->_back_page, $recipe);
    }

    private function add_borders ($p, $idx)
    {
        $yoff = $this->_y_offset;

        $p->drawLine (6 / 8 * 72, 1 * 72 + $yoff,
            (9 / 8) * 72, 1 * 72 + $yoff);
        $p->drawLine ((7 + 3 / 8) * 72, 1 * 72 + $yoff,
            (7 + 6 / 8) * 72, 1 * 72 + $yoff);
        $p->drawLine (6 / 8 * 72, 5 * 72 + $yoff, 
            (9 / 8) * 72, 5 * 72 + $yoff);
        $p->drawLine ((7 + 3 / 8) * 72, 5 * 72 + $yoff,
            (7 + 6 / 8) * 72, 5 * 72 + $yoff);

        $p->drawLine ((1 + 1 / 4) * 72, (1 / 2) * 72 + $yoff,
            (1 + 1 / 4) * 72, (7 / 8) * 72 + $yoff);
        $p->drawLine ((1 + 1 / 4) * 72, (5 + 1 / 8) * 72 + $yoff,
            (1 + 1 / 4) * 72, (5 + 1 / 2) * 72 + $yoff);
        $p->drawLine ((7 + 1 / 4) * 72, (1 / 2) * 72 + $yoff,
            (7 + 1 / 4) * 72, (7 / 8) * 72 + $yoff);
        $p->drawLine ((7 + 1 / 4) * 72, (5 + 1 / 8) * 72 + $yoff,
            (7 + 1 / 4) * 72, (5 + 1 / 2) * 72 + $yoff);
    }


    private function add_text ($fp, $bp, $r)
    {
        $yoff = $this->_y_offset;
        $this->_y_bottom = 72 + $yoff + 6;

        $this->_x_lhcol = (1 + 3 /8) * 72;
        $this->_x_rhcol = (4 + 3 /8) * 72;
        $this->_col_width = (2 + 6 / 8) * 72;

        $nfont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $bfont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $ifont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);

        $this->_front_page = $fp;
        $this->_back_page = $bp;
        $this->_curr_page = $fp;

        $this->_curr_page->setFont ($bfont, 16);
        $this->_curr_page->drawText ($r->get_title (), 
            $this->_x_lhcol, (4 + 3 / 4) * 72 + $yoff, 'utf-8');

        $this->_y = (4 + 3 / 4) * 72 + $yoff;
        $this->_y -= 18;

        $this->_curr_page->setFont ($ifont, 10);
        if ($str = $r->get_yield ())
        {
            $str = "Yield: $str";
            $this->_curr_page->drawText ($str, $this->_x_lhcol, $this->_y, 'utf-8');
            $this->_y -= 12;
        }
        if ($str = $r->get_source ())
        {
            $str = "Source: $str";
            $this->_curr_page->drawText ($str, $this->_x_lhcol, $this->_y, 'utf-8');
            $this->_y -= 12;
        }
        $tags = $r->get_tags ();
        if (count ($tags))
        {
            $xary = array ();
            foreach ($tags as $t)
            {
                $xary[] = $t->get_tag ();
            }
            $str = "Tags: " . join (", ", $xary);
            $this->_curr_page->drawText ($str, $this->_x_lhcol * 72, $this->_y, 'utf-8');
            $this->_y -= 12;
        }

        $this->_curr_page->setFont ($bfont, 10);
        $this->_curr_page->drawText ("Ingredients", $this->_x_lhcol, $this->_y, 'utf-8');
        $this->_y -= 12;

        $this->_y_top = $this->_y;

        $str = $r->get_ingredients ();
        $str = text_utils::fix_fractions ($str);
        $xary = explode ("\n", $this->wrap_text ($str, $nfont, 9, $this->_col_width));
        $this->_x = $this->_x_lhcol;
        foreach ($xary as $str)
        {
            $this->_curr_page->setFont ($nfont, 9);
            $this->_curr_page->drawText ($str, $this->_x, $this->_y, 'utf-8');
            $this->_y -= 10;

            $this->check_y ();
        }

        if ($this->_y != $this->_y_top)
        {
            $this->next_col ();
        }

        $this->_curr_page->setFont ($bfont, 10);
        $this->_curr_page->drawText ("Directions", $this->_x, $this->_y + 12, 'utf-8');

        $this->_y_top = $this->_y;

        $xary = explode ("\n", $this->wrap_text ($r->get_directions (), $nfont, 9, $this->_col_width));
        foreach ($xary as $str)
        {
            $this->_curr_page->setFont ($nfont, 9);
            $this->_curr_page->drawText ($str, $this->_x, $this->_y, 'utf-8');
            $this->_y -= 10;

            $this->check_y ();
        }

        if (!($notes = $r->get_notes ()))
        {
            return;
        }

        $this->_y -= 10;
        $this->_curr_page->setFont ($bfont, 10);
        $this->_curr_page->drawText ("Notes", $this->_x, $this->_y, 'utf-8');
        $this->_y -= 12;

        $xary = explode ("\n", $this->wrap_text ($r->get_notes (), $nfont, 9, $this->_col_width));
        foreach ($xary as $str)
        {
            $this->_curr_page->setFont ($nfont, 9);
            $this->_curr_page->drawText ($str, $this->_x, $this->_y, 'utf-8');
            $this->_y -= 10;

            $this->check_y ();
        }

    }

    protected function check_y ()
    {
        if ($this->_y < $this->_y_bottom)
        {
            $this->next_col ();
        }
    }

    protected function next_col ()
    {
        if ($this->_curr_page == $this->_front_page)
        {
            if ($this->_x == $this->_x_lhcol)
            {
                $this->_y = $this->_y_top;
                $this->_x = $this->_x_rhcol;
            }
            else
            {
                $this->_curr_page = $this->_back_page;
                $this->_y_top = (4 + 3 / 4) * 72 + $this->_y_offset;
                $this->_y = $this->_y_top;
                $this->_x = $this->_x_lhcol;
            }
        }
        else
        {
            $this->_y = $this->_y_top;
            $this->_x = $this->_x_rhcol;
        }
    }


    protected function wrap_text($string, $font, $font_size, $max_width)
    {
        $wrappedText = '';
        $lines = explode("\n", $string);
        foreach($lines as $line) 
        {
            $words = explode(' ',$line);
            $word_count = count($words);
            $i = 0;
            $wrappedLine = '';
            while($i < $word_count)
            {
                /* if adding a new word isn't wider than $max_width,
                we add the word */
                if($this->str_width($wrappedLine . ' ' . $words[$i],
                    $font, $font_size) < $max_width) 
                {
                    if (!empty($wrappedLine)) 
                    {
                        $wrappedLine .= ' ';
                    }
                    $wrappedLine .= $words[$i];
                }
                else
                {
                    $wrappedText .= $wrappedLine . "\n";
                    $wrappedLine = $words[$i];
                }
                $i++ ;
            }
            $wrappedText .= $wrappedLine . "\n";
        }
        return $wrappedText;
    }
    
    /**
    * found here, not sure of the author :
    * http://devzone.zend.com/article/2525-Zend_Pdf-tutorial#comments-2535
    */
    protected function str_width($string, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
}
