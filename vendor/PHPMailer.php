<?php
require_once (PATH_ROOT.'vendor/PHPMailer/class.phpmailer.php');
$hsk_mailtype = '1';
$hsk_smtpauth = '1';
$hsk_sitename = '上海活剧艺术中心';



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
function sendMail($sendto, $subject, $body)
{
	global $mailer;
    $mailer->AddAddress($sendto,'');
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

