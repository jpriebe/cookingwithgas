<?php

class recipe_mailer
{
    public static function send_recipe ($r, $a, $e)
    {
        $subject = "Recipe: " . $r->get_title ();

        $body = self::get_body ($r);

        $filename = strtolower ($r->get_title ());
        $filename = preg_replace ('#[^A-Za-z0-9]+#', '_', $filename);

        $mail = new Zend_Mail ();
        $mail->setBodyText($body);
        $mail->setFrom($a->get_email (), 
            $a->get_fname () . ' ' . $a->get_lname ());
        $mail->addTo($e);
        $mail->setSubject($subject);

        require_once ('recipe_card_generator.php');
        $g = new recipe_card_generator ();
        $pdf = $g->generate_pdf (array ($r));

        $at = $mail->createAttachment ($pdf->render (), 'application/pdf');
        $at->filename = "$filename.pdf";

        $xml = new SimpleXMLElement ('<?xml version="1.0" encoding="utf-8"?><recipes />');

        $rnew = $xml->addChild ('recipe');
        $rnew->addChild ('title', self::xmlsc ((string)$r->get_title ()));
        $rnew->addChild ('ingredients', self::xmlsc ((string)$r->get_ingredients ()));
        $rnew->addChild ('directions', self::xmlsc ((string)$r->get_directions ()));
        $rnew->addChild ('yield', self::xmlsc ((string)$r->get_yield ()));
        $rnew->addChild ('source', self::xmlsc ((string)$r->get_source ()));

        $tnew = $rnew->addChild ('tags');

        foreach ($r->get_tags () as $t)
        {
            $tnew->addChild ('tag', self::xmlsc ((string)$t->get_tag ()));
        }

        $at = $mail->createAttachment ($xml->asXML (), 'text/xml');
        $at->filename = "$filename.xml";

        return ($mail->send ());
    }

    protected static function get_body ($r)
    {
        $body = $r->get_title () . "\n\n";

        if ($yield = $r->get_yield ())
        {
            $body .= "Yield: $yield\n";
        }
        if ($source = $r->get_source ())
        {
            $body .= "Source: $source\n";
        }
        $tags = $r->get_tags ();
        if (count ($tags) > 0)
        {
            $xary = array ();

            foreach ($tags as $t)
            {
                $xary[] = $t->get_tag ();
            }

            $body .= "Tags: " . join (', ', $xary) . "\n";
        }

        $body .= "\nIngredients\n\n";
        $body .= $r->get_ingredients ();

        $body .= "\n\nDirections\n\n";
        $body .= $r->get_directions ();

        if ($notes = $r->get_notes ())
        {
            $body .= "\n\nNotes\n\n";
            $body .= $r->get_notes ();
        }

        return $body;
    }

    private static function xmlsc ($str)
    {
        return str_replace('&#039;', '&apos;', htmlspecialchars($str, ENT_QUOTES));
    }

}
