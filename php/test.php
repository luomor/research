<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 9/18/15
 * Time: 8:41 PM
 */

require(__DIR__ . "/lib/Luomor/Util.php");

use Luomor\Util;

echo Util::generateCode(1, "A", Util::BIG_INVITE_CODE_LEN) . "\n";// A1000D0E1Q
echo Util::getIdByCodeThirdparty("A1000D0E1Q") . "\n";// 1

