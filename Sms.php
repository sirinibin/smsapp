<?php

class Sms
{

    private $recipients = [];

    private $messages = [];

    private $originator = 'MessageBird';

    public $messageLength=0;

    public $chars_split_at=153;

    function __construct($recipients, $message, $originator)
    {
        $this->recipients = $recipients;
        $this->messages[] = $message;
        $this->$originator = $originator;

        $this->splitMessage();

        if (!file_exists("status.txt")) {
            file_put_contents("status.txt", "0");
        }


    }

    public function send()
    {
        $processing = (string)file_get_contents("status.txt"); //checking if there is any other user sending sms's to avoid concurrent api usage.

        while ($processing == '1') {
            $processing = (string)file_get_contents("status.txt");
        }


        if (!isset($this->messages[0])) {
            file_put_contents("status.txt", "0");
            return false;
        }


        if (strlen($this->messages[0]) <= 160 && !isset($this->messages[1])) {

            return $this->sendMessage();
        } else {

            return $this->sendSplitMessage();
        }
    }

    private function splitMessage()
    {

        if (strlen($this->messages[0]) <= 160)
            return false;

        $message = trim($this->messages[0]);

        $this->messages = [];

        $i = 0;
        while ($i < 1377) {

            $str = trim(substr($message, $i, $this->chars_split_at)); // 306 + 153= 459,459 + 153= 612,612 + 153= 765,765 + 153= 918,765 + 153= 1071,765 + 153= 1224,1224 + 153= 1377
            if (!empty($str))
                $this->messages[] = $str;
            $i += $this->chars_split_at;
        }

        return $this->messages;


    }

    private function sendMessage()
    {
        file_put_contents("status.txt", "1");
        $MessageBird = new \MessageBird\Client(ACCESS_KEY); // Set your own API access key here.

        $Message = new \MessageBird\Objects\Message();
        $Message->originator = $this->originator;
        $Message->recipients = $this->recipients;
        $Message->body = $this->messages[0];
        $this->messageLength=strlen($this->messages[0]);

        try {
            $MessageResult = $MessageBird->messages->create($Message);
            file_put_contents("status.txt", "0");
            return $MessageResult;

        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            file_put_contents("status.txt", "0");
            // That means that your accessKey is unknown

            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad request', true, 400);
            echo json_encode(['status' => 0, 'error' => 'wrong login'], JSON_PRETTY_PRINT);
            exit;

        } catch (\MessageBird\Exceptions\BalanceException $e) {
            file_put_contents("status.txt", "0");
            // That means that you are out of credits, so do something about it.
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad request', true, 400);
            echo json_encode(['status' => 0, 'error' => 'no balance'], JSON_PRETTY_PRINT);
            exit;

        } catch (\Exception $e) {
            file_put_contents("status.txt", "0");
            $error = $e->getMessage();
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad request', true, 400);
            echo json_encode(['status' => 0, 'error' => $error], JSON_PRETTY_PRINT);
            exit;

        }

        file_put_contents("status.txt", "0");
        return false;


    }

    private function sendSplitMessage()
    {

        file_put_contents("status.txt", "1");
        $MessageResult = [];
        foreach ($this->messages as $k => $m) {

            if (empty($m))
                continue;

            $this->messageLength+=strlen($m);

            $MessageBird = new \MessageBird\Client(ACCESS_KEY); // Set your own API access key here.

            $Message = new \MessageBird\Objects\Message();
            $Message->originator = $this->originator;

            $Message->type = "binary";
            $Message->recipients = $this->recipients;
            $Message->body = $m;


            $message_count = count($this->messages);
            if ($message_count < 10) {
                $message_count = "0" . $message_count;
            }

            $message_index = ($k + 1);

            if ($message_index < 10) {
                $message_index = "0" . $message_index;
            }

            //Field 1 (1 octet): Length of User Data Header, in this case 05.
            //Field 2 (1 octet): Information Element Identifier, equal to 00 (Concatenated short messages, 8-bit reference number)
            //Field 3 (1 octet): Length of the header, excluding the first two fields; equal to 03
            //Field 4 (1 octet): 00-FF, CSMS reference number, must be same for all the SMS parts in the CSMS
            //Field 5 (1 octet): 00-FF, total number of parts.
            //Field 6 (1 octet): 00-FF, this part's number in the sequence.

            $udh = '05000307' . $message_count . $message_index;

            $Message->typeDetails = [
                "udh" => $udh
            ];
            $Message->setBinarySms($udh,$m);

            try {
                $MessageResult[] = $MessageBird->messages->create($Message);


            } catch (\MessageBird\Exceptions\AuthenticateException $e) {
                // That means that your accessKey is unknown
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad request', true, 400);
                echo json_encode(['status' => 0, 'error' => 'wrong login'], JSON_PRETTY_PRINT);
                exit;


            } catch (\MessageBird\Exceptions\BalanceException $e) {
                // That means that you are out of credits, so do something about it.
                file_put_contents("status.txt", "0");

                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad request', true, 400);
                echo json_encode(['status' => 0, 'error' => 'no balance'], JSON_PRETTY_PRINT);
                exit;

            } catch (\Exception $e) {
                file_put_contents("status.txt", "0");
                $error = $e->getMessage();
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad request', true, 400);
                echo json_encode(['status' => 0, 'error' => $error], JSON_PRETTY_PRINT);
                exit;
            }

            sleep(1);

        }

        file_put_contents("status.txt", "0");

        return $MessageResult;


    }

    public function getMessages()
    {
        return $this->messages;
    }
}