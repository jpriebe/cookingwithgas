<?php

class text_utils 
{
    public static function fix_fractions ($str)
    {
        $str = str_replace ('1/2', '½', $str);

        #### these don't seem to be available in some fonts
        #$str = str_replace ('1/3', '⅓', $str);
        #$str = str_replace ('2/3', '⅔', $str);

        $str = str_replace ('1/4', '¼', $str);
        $str = str_replace ('3/4', '¾', $str);

        $str = str_replace ('1/8', '⅛', $str);

        return $str;
    }
}
