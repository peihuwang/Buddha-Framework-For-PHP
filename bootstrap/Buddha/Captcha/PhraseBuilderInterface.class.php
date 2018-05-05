<?php

/**
 * Interface Buddha_Captcha_PhraseBuilderInterface
 */
interface Buddha_Captcha_PhraseBuilderInterface
{
    /**
     * Generates  random phrase of given length with given charset
     */
    public function build($length, $charset);

    /**
     * "Niceize" a code
     */
    public function niceize($str);
}
