<?php
/**
 * Created by PhpStorm.
 * User: Pathologic
 * Date: 22.05.2015
 * Time: 20:43
 */

if (!IN_MANAGER_MODE) {
    die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}

include_once(MODX_BASE_PATH.'assets/tvs/ymap/lib/ymap.class.php');
$ymap = new \YMap\YMap (
    $modx,
    $row
);

echo $ymap->render();