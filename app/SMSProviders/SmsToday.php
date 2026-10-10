<?php 

namespace App\SMSProviders;

class SmsToday
{
    public function send($data)
    {
        
        $endpoint = "https://api.smstoday.net/send";
        $ch = curl_init();
        $array_post = http_build_query(array(
            'text'=> $data['message'],
            'numbers'=> $data['numbers'],
            'api_key'=> env('SMSTODAY_API_KEY', ''),
            'password'=> env('SMSTODAY_PASSWORD', ''),
            'from'=> env('SMSTODAY_SENDER_ID', '')
        ));

        curl_setopt($ch, CURLOPT_URL,$endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$array_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        //dd($server_output);
        $message = "SMS sent successfully";
    }
}