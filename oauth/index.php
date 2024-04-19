<?php
session_start();
/**
 * @author Denis CLAVIER <clavierd at gmail dot com>
 * A modified verion by dimst23
 */


// include our LDAP object
//require_once __DIR__.'/LDAP/LDAP.php';
//require_once __DIR__.'/LDAP/config_ldap.php';

$prompt_template = new DOMDocument();
$prompt_template->loadHTMLFile('form_prompt.html');


function messageShow($html_template, $message = 'No Msg') {
    $modification_node = $html_template->getElementsByTagName('div')->item(5);
    $page_fragment = $html_template->createDocumentFragment();
    $page_fragment->appendXML($message);

    $modification_node->appendChild($page_fragment);

    echo $html_template->saveHTML();
}


// Verify all fields have been filled
if (empty($_POST['user']) || empty($_POST['password']))
{
    if (empty($_POST['user'])) {
        messageShow($prompt_template, 'Username field can\'t be empty.');
    } else {
        messageShow($prompt_template, 'Password field can\'t be empty.');
    }
}
else
{
    // Check received data length (to prevent code injection)
    if (strlen($_POST['user']) > 64)
     {
        messageShow($prompt_template, 'Username has incorrect format ... Please try again');
    }
    elseif (strlen($_POST['password']) > 64)
    {
        messageShow($prompt_template, 'Password has incorrect format ... Please try again');
    }
    else
       {

        $url = 'https://intranet.hka-iwi.de/iwii/REST/credential/v2/info';
        $username = $_POST['user'];
        $password = $_POST['password'];

        // Initialize cURL
        $curl = curl_init($url);

        // Set options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");

        // Execute cURL request
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Check for errors
        if($status_code == 401) {
            $error = curl_error($curl);
            $authenticated = false;
        } else if($status_code == 200) {
            // Process the response
            $authenticated = true;
        }

        // Close cURL session
        curl_close($curl);

           // Remove every html tag and useless space on username (to prevent XSS)
        //$user=strtolower(strip_tags(htmlspecialchars(trim($_POST['user']))));
        //$password=$_POST['password'];

        // Open a LDAP connection
        //$ldap = new LDAP($ldap_host,$ldap_port,$ldap_version,$ldap_start_tls);

        // Check user credential on LDAP
        //try{
        //    $authenticated = $ldap->checkLogin($user,$password,$ldap_search_attribute,$ldap_filter,$ldap_base_dn,$ldap_bind_dn,$ldap_bind_pass);
        //}
        //catch (Exception $e)
        //{
        //    $authenticated = false;
        //}

        // If user is authenticated
        if ($authenticated)
        {
            $_SESSION['uid']=$username;

            // If user came here with an autorize request, redirect him to the authorize page. Else prompt a simple message.
            if (isset($_SESSION['auth_page']))
            {
                $auth_page=$_SESSION['auth_page'];
                header('Location: ' . $auth_page);
                 exit();
            }
             else
             {
                messageShow($prompt_template, 'Congratulation you are authenticated ! <br /><br /> However there is nothing to do here ...');
            }
        }
        // check login on LDAP has failed. Login and password were invalid or LDAP is unreachable
        else
        {
            messageShow($prompt_template, 'Authentication failed ... Check your username and password.<br />If the error persists contact your administrator.<br /><br />');
        }
    }
}
