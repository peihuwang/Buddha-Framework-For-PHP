<?php
/**
 * Extends the Buddha_Base_Exception class with the intent of using
 */
class Buddha_Exception_Unsupport extends Buddha_Base_Exception{

    public function __construct($messages = array(
        'message' => '改功能不支持',
        'summary' => '这是Buddha_Exception_Unsupport异常',
        'resolution' => '不要调用错误的函数，因为此函数会抛出异常'
    ), $code = 1, Exception $previous = null)
    {
        if ( is_array($messages))
        {
            $this->setErrorDataFromArray($messages);
            $messages = isset($messages['message']) ? $messages['message'] : null;
        }
        parent::__construct($messages, $code, $previous);
    }

}