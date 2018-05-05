<?php
class Buddha_Base_Exception extends Exception
{
    /**
     * @var null|string
     */
    protected $summary;
    /**
     * @var null|string
     */
    protected $resolution;
    /**
     * Construct the exception
     *
     * @param  string|array $messages
     * @param  int $code
     * @param  Exception $previous
     * @return void
     */
    public function __construct($messages = null, $code = 0, Exception $previous = null)
    {
        if ( is_array($messages))
        {
            $this->setErrorDataFromArray($messages);
            $messages = isset($messages['message']) ? $messages['message'] : null;
        }
        parent::__construct($messages, $code, $previous);
    }
    /**
     * Get exception summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }
    /**
     * Set exception summary.
     *
     * @return string
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
        return $this;
    }
    /**
     * Get exception resolution.
     *
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }
    /**
     * Set exception resolution.
     *
     * @return string
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;
        return $this;
    }


    public function printJson(){

           $result['errcode'] = $this->getCode();
           $result['errmsg']  = $this->getMessage();
           $result['data']  = $this->__toString();
           $result['action']  = $this->getFile();


        if(isset($_REQUEST['callback'])){
            echo $_REQUEST['callback'] . "(" . $result . ")";
            ;
        }else{
            echo $result =str_replace("\\/", "/",  json_encode($result));
        }
        exit
    ;
    }
    /**
     * String representation of the exception with magic method.
     *
     * @return string
     */
    public function __toString()
    {
        $summary = $this->getSummary();
        $resolution = $this->getResolution();
        $message = $this->getMessage();
        $line = $this->getLine();
        $code = $this->getCode();

        $str = '';


        if ( ! is_null($code))
        {
            $str .= 'Code : '.$code . "\n</br>";
        }
        if ( ! is_null($summary))
        {
            $str .= 'Summary : '.$summary . "\n</br>";
        }
        if ( ! is_null($resolution))
        {
            $str .= 'Resolution : '.$resolution . "\n</br>";
        }

        if ( ! is_null($message))
        {
            $str .= 'Message : '.$message . "\n</br>";
        }

        if ( ! is_null($line))
        {
            $str .= 'ErrorLine : '.$line . "\n</br>";
        }

        return $str.parent::__toString();
    }
    /**
     * Set exception error messages from array
     *
     * @param array $messages
     * @return void
     */
    protected function setErrorDataFromArray($messages)
    {
        if ( isset($messages['summary']))
        {
            $this->setSummary($messages['summary']);
        }
        if ( isset($messages['resolution']))
        {
            $this->setResolution($messages['resolution']);
        }
    }
}

