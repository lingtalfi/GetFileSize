<?php


use Tim\TimServer\OpaqueTimServer;
use Tim\TimServer\TimServerInterface;

require_once __DIR__ . "/../../../../init.php";


OpaqueTimServer::create()
    ->start(function (TimServerInterface $server) {
        if (isset($_POST['file'])) {
            $file = $_POST['file'];
            $human = (array_key_exists('human', $_POST)) ? true : false;


            // http://php.net/manual/fr/function.filesize.php#114952
            function remote_filesize($url)
            {
                static $regex = '/^Content-Length: *+\K\d++$/im';
                if (!$fp = @fopen($url, 'rb')) {
                    return false;
                }
                if (
                    isset($http_response_header) &&
                    preg_match($regex, implode("\n", $http_response_header), $matches)
                ) {
                    return (int)$matches[0];
                }
                return strlen(stream_get_contents($fp));
            }


            // http://php.net/manual/fr/function.filesize.php#106569
            function human_filesize($bytes, $decimals = 2)
            {
                $sz = 'BKMGTP';
                $factor = floor((strlen($bytes) - 1) / 3);
                return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
            }


            if (false !== ($size = remote_filesize($file))) {

                if ($human) {
                    $size = human_filesize($size);
                }

                $server->success($size);
            }
            else {
                $server->error("Could not guess the file size");
            }
        }
        else {
            $server->error("Invalid data, missing file");
        }
    })->output();
