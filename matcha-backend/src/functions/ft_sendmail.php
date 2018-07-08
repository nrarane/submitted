<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require '../vendor/autoload.php';

    
    // if (ft_sendmail('nrarane@student.wethinkcode.co.za', 'lonwabo', 'no-reply', 'this is a confirmation email')){
    //     echo "Ok";
    // }else
    //     echo "KO";

    function ft_sendmail($to, $name, $subject, $message){
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers = implode("\r\n", $headers);

        try{
            if (mail($to, $subject, $message, $headers))
                return (true);
        }catch(Exception $exc){}

        /*$mail = new PHPMailer(true);

        try{
            //Server settings
            /**\/
            //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'mkgosise@wethinkcode.co.za';                 // SMTP username
            $mail->Password = '';                           // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to
            /**\/

            //Recipients
            //$mail->setFrom(Config::get('app/email'), Config::get('app/name'));
            $mail->addAddress($to, $name);     // Add a recipient
            
            /*
            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            *\/

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            return (true);
        }catch(Exception $exc){
            ft_put_error($exc->getMessage());
        }*/
        return (false);
    }

?>