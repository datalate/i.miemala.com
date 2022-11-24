<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!--<link rel="icon" href="../favicon.png">-->  <!--196x196x-->
        <link rel="stylesheet" type="text/css" href="images.css">
        <title>HQ Images</title>
    </head>
    <body>
<?php

require_once("../mysql.php");
$db = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
if ($db->connect_errno > 0) {
    echo "Unable to connect to database: ".$db->connect_error;
}
else {
    if (!$db->set_charset("utf8")) {
        echo "Failed to load character set: ".$db->error;
    }
    else {
        $sql = "SELECT `code`,`fname` FROM `data` ORDER BY `id` DESC";
        if (!$result = $db->query($sql)) {
            echo "Query failed: ".$db->error;
        }
        echo '<table id="images">';
        while ($row = $result->fetch_assoc()) {
            $fname = $row["fname"];
            printf('<tr><td><a href="../%s">%s</a></td></tr>',
                   $row["code"], $row["fname"]);
        }
        echo '</table>';
        $result->free();
        $db->close();
    }
}

?>
    </body>
</html>
