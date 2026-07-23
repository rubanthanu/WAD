<?php

class ContactController extends Controller
{
    public function handle(): void
    {
        $method = $this->getMethod();

        if ($method === 'POST') {
            $this->store();
        } else {
            Response::error("Method not allowed.", 405);
        }
    }

    private function store(): void
    {
        $data = $this->getInput();

        if (!Validator::required($data, ['name', 'email', 'message'])) {
            Response::error("Name, email, and message are required.", 400);
        }

        $email = trim($data['email']);
        if (!Validator::email($email)) {
            Response::error("Please provide a valid email address.", 400);
        }

        $name    = trim($data['name']);
        $message = trim($data['message']);

        try {
            $contactModel = new Contact($this->db);
            $contactModel->setName($name);
            $contactModel->setEmail($email);
            $contactModel->setMessage($message);

            if ($contactModel->save()) {
                Response::success("Thank you for contacting us! Your message has been received.");
            } else {
                Response::error("Failed to save message.", 500);
            }
        } catch (Exception $e) {
            Response::error("An error occurred while sending your message.", 500);
        }
    }
}
