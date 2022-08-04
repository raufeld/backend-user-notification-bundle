<?php

namespace Alnv\ContaoBackendUserNotificationBundle\Library;

class Helpers {

    public static function generatePassword() {

        $strCode = '';
        $intUnits = 16;
        $strCharPool = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ$%";
        for($u=0;$u<$intUnits;$u++) {
            $intRanPos = mt_rand(0, strlen($strCharPool)-1);
            $strCode .= $strCharPool[$intRanPos];
        }

        return $strCode;
    }
}