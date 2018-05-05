<?php
interface Buddha_Validator_TranslatorInterface
{
    /**
     * translator.
     *
     * @param $key message key.
     *
     * @return string
     */
    public function trans($key);
}