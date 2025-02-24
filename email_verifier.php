<?php
set_time_limit(0);

class EmailVerifier {
    public $email, $verifier_email, $port;
    private $mx, $connect, $errors;
    private $_yahoo_signup_page_url = 'https://login.yahoo.com/account/create?specId=yidReg&lang=en-US&src=&done=https%3A%2F%2Fwww.yahoo.com&display=login';
    private $_yahoo_signup_ajax_url = 'https://login.yahoo.com/account/module/create?validateField=yid';
    private $_yahoo_domains = ['yahoo.com'];
    private $_hotmail_signin_page_url = 'https://login.live.com/';
    private $_hotmail_username_check_url = 'https://login.live.com/GetCredentialType.srf?wa=wsignin1.0';
    private $_hotmail_domains = ['hotmail.com', 'live.com', 'outlook.com', 'msn.com'];
    private $page_content, $page_headers;
    // Collect role prefixes from GitHub, search engines, and add them here.
    private $role_prefixes = ['admin', 'support', 'info', 'contact', 'webmaster', 'sales', 'help', 'service'];
    // Also collect temp|disposable domains from GitHub, search engines, and add them here.
    private $disposable_domains = ['mailinator.com', '10minutemail.com', 'guerrillamail.com', 'yopmail.com'];

    public function __construct($verifier_email = null, $port = 25, $email = null) {
        if ($email && $verifier_email && $port) {
            $this->verifier_email = $verifier_email;
            $this->port = $port;
            $this->email = $email;
        }
    }

    public function verify() {
        $response = [
            "email" => $this->email,
            "format_valid" => $this->is_format_valid($this->email),
            "mx_found" => false,
            "smtp_check" => false,
            "catch_all" => false,
            "role" => $this->is_role_email(),
            "disposable" => $this->is_disposable_email()
        ];

        if (!$response["format_valid"]) return $response;

        $domain = $this->get_domain($this->email);
        $response["mx_found"] = $this->find_mx($this->email);

        if (!$response["mx_found"] || !$this->is_format_valid($this->verifier_email) || !$this->find_mx($this->verifier_email)) {
            return $response;
        }

        if (in_array(strtolower($domain), $this->_yahoo_domains)) {
            $response["smtp_check"] = $this->validate_yahoo();
        } else if (in_array(strtolower($domain), $this->_hotmail_domains)) {
            $response["smtp_check"] = $this->validate_hotmail();
        } else {
            $response["smtp_check"] = $this->validate_smtp();
            sleep(1);
            $response["catch_all"] = $this->is_catch_all();
        }

        return $response;
    }

    private function is_format_valid($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function get_domain($email) {
        return explode('@', $email)[1];
    }

    private function find_mx($email) {
        $domain = $this->get_domain($email);
        return getmxrr($domain, $mxhosts) ? (bool)($this->mx = $mxhosts[0]) : false;
    }

    private function validate_smtp() {
        $this->connect_mx();
        if (!$this->connect) return false;

        $commands = [
            "EHLO " . $this->get_domain($this->verifier_email),
            "MAIL FROM: <" . $this->verifier_email . ">",
            "RCPT TO: <" . $this->email . ">",
            "QUIT"
        ];

        foreach ($commands as $cmd) {
            fputs($this->connect, $cmd . "\r\n");
            $response = fgets($this->connect);
            if ($cmd == "RCPT TO: <" . $this->email . ">" && preg_match("/^[45]\d{2}/", $response)) {
                $this->add_error('150', 'RCPT TO command failed: ' . $response);
                return false;
            }
        }
        fclose($this->connect);
        return true;
    }

    private function connect_mx() {
        $this->connect = @fsockopen($this->mx, $this->port);
    }

    private function is_catch_all() {
        $random_email = "random-" . uniqid() . "@" . $this->get_domain($this->email);
        $this->connect_mx();
        if (!$this->connect) return false;

        fputs($this->connect, "HELO " . $this->get_domain($this->verifier_email) . "\r\n");
        fgets($this->connect);
        fputs($this->connect, "MAIL FROM: <" . $this->verifier_email . ">\r\n");
        fgets($this->connect);
        fputs($this->connect, "RCPT TO: <" . $random_email . ">\r\n");
        $to = fgets($this->connect);
        fputs($this->connect, "QUIT");
        fclose($this->connect);
        return preg_match("/^250/i", $to) ? true : false;
    }

    private function is_role_email() {
        return in_array(explode('@', $this->email)[0], $this->role_prefixes);
    }

    private function is_disposable_email() {
        return in_array($this->get_domain($this->email), $this->disposable_domains);
    }

    private function validate_yahoo() {
        $this->fetch_page('yahoo');
        $cookies = $this->get_cookies();
        $fields = $this->get_fields();
        $fields['yid'] = str_replace('@yahoo.com', '', strtolower($this->email));
        $response = $this->request_validation('yahoo', $cookies, $fields);
        $response_errors = json_decode($response, true)['errors'];

        foreach ($response_errors as $err) {
            if ($err['name'] == 'yid' && $err['error'] == 'IDENTIFIER_EXISTS') {
                return true;
            }
        }
        return false;
    }

    private function validate_hotmail() {
        $this->fetch_page('hotmail');
        $cookies = $this->get_cookies();
        $this->fetch_page('hotmail', implode(' ', $cookies));
        $cookies = $this->get_cookies();
        $fields = $this->prep_hotmail_fields($cookies);
        $response = $this->request_validation('hotmail', $cookies, $fields);
        $json_response = json_decode($response, true);

        return !$json_response['IfExistsResult'];
    }

    private function fetch_page($service, $cookies = '') {
        $url = $service == 'yahoo' ? $this->_yahoo_signup_page_url : $this->_hotmail_signin_page_url;
        $opts = $cookies ? ['http' => ['method' => "GET", 'header' => "Accept-language: en\r\nCookie: " . $cookies . "\r\n"]] : [];
        $context = $opts ? stream_context_create($opts) : null;
        $this->page_content = file_get_contents($url, false, $context);
        $this->page_headers = $http_response_header;
    }

    private function get_cookies() {
        $cookies = [];
        foreach ($this->page_headers as $hdr) {
            if (preg_match('/^Set-Cookie:\s*(.*?;).*?$/i', $hdr, $matches)) {
                $cookies[] = $matches[1];
            }
        }
        return $cookies;
    }

    private function get_fields() {
        $dom = new DOMDocument();
        $fields = [];
        if (@$dom->loadHTML($this->page_content)) {
            $xp = new DOMXpath($dom);
            foreach ($xp->query('//input') as $node) {
                $fields[$node->getAttribute('name')] = $node->getAttribute('value');
            }
        }
        return $fields;
    }

    private function request_validation($service, $cookies, $fields) {
        $url = $service == 'yahoo' ? $this->_yahoo_signup_ajax_url : $this->_hotmail_username_check_url;
        $headers = [
            'Origin: https://login.' . ($service == 'yahoo' ? 'yahoo.com' : 'live.com'),
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
            'Content-type: application/' . ($service == 'yahoo' ? 'x-www-form-urlencoded' : 'json') . '; charset=UTF-8',
            'Accept: */*',
            'Referer: ' . ($service == 'yahoo' ? $this->_yahoo_signup_page_url : $this->_hotmail_signin_page_url),
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.8,ar;q=0.6',
            'Cookie: ' . implode(' ', $cookies)
        ];

        $postdata = $service == 'yahoo' ? http_build_query($fields) : json_encode($fields);
        $opts = ['http' => ['method' => 'POST', 'header' => $headers, 'content' => $postdata]];
        return file_get_contents($url, false, stream_context_create($opts));
    }

    private function prep_hotmail_fields($cookies) {
        foreach ($cookies as $cookie) {
            list($key, $val) = explode('=', $cookie, 2);
            if ($key == 'uaid') {
                return ['uaid' => $val, 'username' => strtolower($this->email)];
            }
        }
        return [];
    }

    private function add_error($code, $message) {
        $this->errors[] = ['code' => $code, 'message' => $message];
    }
}
?>
