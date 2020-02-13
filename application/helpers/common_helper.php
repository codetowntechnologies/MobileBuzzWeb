<?php
function get_encrypted_pass($password)
{
    return sha1(md5($password));
}

function qrcode($data, $type)
{
    include_once(APPPATH . 'third_party/phpqrcode/qrlib.php');
    //include "qrlib.php";

    //set it to writable location, a place for temp generated PNG files
    //$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //$PNG_TEMP_DIR = dirname(__FILE__);
    $PNG_TEMP_DIR = FCPATH . 'assets/uploads/';
    //echo $PNG_TEMP_DIR; //die;

    //html PNG location prefix
    $PNG_WEB_DIR = $type . '/qrcode/';

    $PNG_TEMP_DIR = $PNG_TEMP_DIR . $PNG_WEB_DIR;

    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)) {
        @mkdir($PNG_TEMP_DIR, 0777, true);
        @chmod($PNG_TEMP_DIR, 0777);
    }
    //echo $PNG_TEMP_DIR; //die;

    $filename = 'test.png';

    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'Q';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L', 'M', 'Q', 'H')))
        $errorCorrectionLevel = $_REQUEST['level'];

    $matrixPointSize = 3;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);

    if (isset($_REQUEST['data'])) {

        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');

        // user data
        $filename = md5($_REQUEST['data'] . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        $data = $_REQUEST['data'];
    } else {
        //default data
        $filename = md5($data . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
    }

    $filepath = $PNG_TEMP_DIR . $filename;
    QRcode::png($data, $filepath, $errorCorrectionLevel, $matrixPointSize, 2);
    return $filename;

    $imgUrl = site_url() . image('uploads/' . $PNG_WEB_DIR . basename($filename));

    //display generated file
    //echo $imgUrl;
    //echo '<img src="'.$imgUrl.'" /><hr/>';
}