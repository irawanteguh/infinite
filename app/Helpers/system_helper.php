<?php

if (! function_exists('generateuuid')) {
    function generateuuid($data = null)
    {
        $data = $data ?? random_bytes(16);

        assert(strlen($data) === 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf(
            "%s%s-%s-%s-%s-%s%s%s",
            str_split(bin2hex($data), 4)
        );
    }
}

if (! function_exists('encodedata')) {
    function encodedata($data)
    {
        $i2 = 0;
        $s = "";
        $length = strlen($data);

        $pass = $data[$length - 1]
              . substr($data, 1, $length - 2)
              . $data[0];

        for ($i = 0; $i < $length; $i++) {
            $i2++;

            if ($i2 === 1) {
                $s .= "";
                $i2 = 0;
            }

            $num = dechex(ord($pass[$i]));
            $s .= $num;
        }

        return $s;
    }
}

if (! function_exists('decodedata')) {
    function decodedata($data)
    {
        $length = strlen($data);
        $res = '';

        if ($length % 2 !== 0) {
            return '';
        }

        for ($i = 0; $i < $length; $i += 2) {
            $char = $data[$i] . $data[$i + 1];
            $res .= chr(hexdec($char));
        }

        if ($res === '') {
            return '';
        }

        $decodedLength = strlen($res);

        return $res[$decodedLength - 1]
             . substr($res, 1, $decodedLength - 2)
             . $res[0];
    }
}