<?php

class Api
{

    public $recipients;
    public $originator;
    public $message;

    private $request;


    private $error_response = ['status' => 0];
    private $success_response = ['status' => 1];
    private $errors = [];


    function __construct()
    {
        $this->request = json_decode(file_get_contents('php://input'), true);

        if (isset($this->request['recipient'])) {
            if (!is_array($this->request['recipient']))
                $this->recipients = [$this->request['recipient']];
            else
                $this->recipients = $this->request['recipient'];
        }

        if (isset($this->request['originator'])) {
            $this->originator = $this->request['originator'];
        }

        if (isset($this->request['message'])) {
            $this->message = $this->request['message'];
        }

        $this->validateData();

    }

    private function validateData()
    {
        $valid = true;
        if (!isset($this->request['recipient']) ||
            empty($this->request['recipient']) ||
            count($this->request['recipient']) == 0
        ) {
            $this->errors[] = ['recipient' => 'Recipient is missing'];
            $valid = false;
        }
        if (!isset($this->request['originator']) || empty($this->request['originator'])) {
            $this->errors[] = ['originator' => 'Originator is missing'];
            $valid = false;

        }

        if (!isset($this->request['message']) || empty($this->request['message'])) {
            $this->errors[] = ['message' => 'Message is missing'];
            $valid = false;

        }

        if (!$valid)
            $this->sendErrorResponse();
        else
            return true;
    }

    public function sendErrorResponse()
    {
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad request', true, 400);
        $this->error_response = array_merge($this->error_response, ['errors' => $this->errors]);
        echo json_encode($this->error_response, JSON_PRETTY_PRINT);
        exit;
    }

    public function sendResponse($data,$message_length)
    {
        header('Content-Type: application/json ');
        $this->success_response = array_merge($this->success_response, ['total_message_length'=>$message_length,'result' => (array)$data]);
        echo json_encode($this->success_response, JSON_PRETTY_PRINT);
        exit;
    }
}