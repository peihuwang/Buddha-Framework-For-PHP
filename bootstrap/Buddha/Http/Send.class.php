<?php

/**
 * Class Buddha_Http_Send
 */
class Buddha_Http_Send{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Input
     */
    public static function getInstance($options)
    {
        if (self::$_instance === null) {
            $createObj=  new self();
            if (is_array($options))
            {
                foreach ($options as $option => $value)
                {
                    $createObj->$option = $value;
                }
            }
            self::$_instance =$createObj;
        }
        return self::$_instance;
    }
    public function __construct(){

    }

    /**
     * @param $sendto
     * @param $subject
     * @param $body
     * @return bool|string
     */
   public static  function sendMail($sendto, $subject, $body)
   {

       require_once (PATH_ROOT.'vendor/PHPMailer/class.phpmailer.php');
       $hsk_mailtype = '1';
       $hsk_smtpauth = '1';
       $hsk_sitename = '营销中心';

       $hsk_smtphost= Buddha::$buddha_array['cache']['config']['hsk_smtphost'];
       $hsk_smtpuser= Buddha::$buddha_array['cache']['config']['hsk_smtpuser'];
       $hsk_smtppw= Buddha::$buddha_array['cache']['config']['hsk_smtppw'];
       $hsk_smtpport= Buddha::$buddha_array['cache']['config']['hsk_smtpport'];

       $mailer = new PHPMailer();

       if ($hsk_mailtype)
       {
           $mailer->IsSMTP();
           $mailer->Host = $hsk_smtphost;
           if ($hsk_smtpauth)
           {
               $mailer->SMTPAuth = TRUE;
           }
           $mailer->Username = $hsk_smtpuser;
           $mailer->Password = $hsk_smtppw;
           if ($hsk_smtpport)
           {
               $mailer->Port = $hsk_smtpport;
           }
       }
       else
       {
           $mailer->IsMail();
       }
       $mailer->From = $hsk_smtpuser;
       $mailer->FromName = $hsk_sitename;
       $mailer->CharSet = 'utf-8';
       $mailer->Encoding = 'base64';
       $mailer->IsHTML(TRUE);
       $mailer->AltBody ='To view the message, please use an HTML compatible email viewer!';

        $mailer->AddAddress($sendto);
        $mailer->Subject = $subject;
        $mailer->Body = $body;


        if($mailer->Send())
        {
            return TRUE;
        }
        else
        {
            return $mailer->ErrorInfo;
        }
    }


}