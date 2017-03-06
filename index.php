<?php
$folderPath = "img/";

if (!empty($_FILES)) {
    // -- Image uploading --
    $file = $_FILES["file"];
    
    if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        die("Error");
    }
    
    if (!isset($file["error"]) || is_array($file["error"])) {
        die("Error");
    }
    
    if ($file["error"] != UPLOAD_ERR_OK) {
        die("Error");
    }
    
    // Allow only image files
    $imgSize = getimagesize($file["tmp_name"]);
    if (!$imgSize) {
        die("Not image");
    }
    
    $name = $file["name"];
    $origFileName = pathinfo($name, PATHINFO_FILENAME);
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    
    // Get unique name
    $i = 1;
    while (file_exists($folderPath.$name)) {
        if ($extension != "") {
            $name = (string)$origFileName."-".$i.".".$extension;
        }
        else {
            $name = (string)$origFileName."-".$i;
        }
        
        $i++;
    }
    
    $db = new mysqli("localhost", "www", "www", "www");
    if ($db->connect_errno > 0) {
        die("Unable to connect to database: ".$db->connect_error);
    }
    if (!$db->set_charset("utf8")) {
        die("Failed to load character set: ".$db->error);
    }
    
    // Generate codes using md5 hash, base64 used for mixed case alphabet
    $md5 = md5($name);
    $code = substr(base64_encode($md5), 0, 4);
    $statement = $db->prepare("SELECT `id` FROM `sharex` WHERE `code` = ?
                               LIMIT 1") or die("prepare()");
                               
    $statement->bind_param("s", $code) or die("bind_param()");
    $statement->execute() or die("execute() 1");
    $statement->store_result();
    $statement->bind_result($result) or die("bind_result()");
    
    // Collision handling for the same codes
    while ($statement->fetch()) {
        $statement->free_result();
        
        $md5 = md5($md5);
        $code = substr(base64_encode($md5), 0, 4); // Get new code
        
        $statement->execute() or die("execute() 2");
        $statement->store_result() or die("store_result()");
    }
    
    $statement->close();
    
    $sql = "INSERT INTO sharex(code, fname, time, ip) VALUES(?, ?, ?, ?)";
    $statement = $db->prepare($sql) or die("prepare()");
    $statement->bind_param("ssis", $code, $name, time(),
                           $_SERVER["REMOTE_ADDR"]) or die("bind_param()");
    $statement->execute() or die("execute() 3: ".$db->error);
    $statement->close();

    $db->close();
    
    move_uploaded_file($file["tmp_name"], $folderPath . $name);
                    
    // Redirect to upload url
    header("Location: http://i.miemala.com/".$code);
}
else if (isset($_GET["i"]) and !empty($_GET["i"])) {
    // -- Image displaying --
    
    $db = new mysqli("localhost", "www", "www", "www");
    if ($db->connect_errno > 0) {
        die("Unable to connect to database: ".$db->connect_error);
    }
    if (!$db->set_charset("utf8")) {
        die("Failed to load character set: ".$db->error);
    }
    
    if ($_GET["i"] === "random") {
        // Display random image
        
        $sql = "SELECT `fname` FROM `sharex` ORDER BY RAND() LIMIT 1";
        if (!$result = $db->query($sql)) {
            die("Query failed: ".$db->error);
        }
        
        if ($result->num_rows == 0) {
            die("No images uploaded");
        }
        else {
            $row = $result->fetch_assoc();
            $fname = $row["fname"];
        }

        $result->free();
    }
    else {
        // Get image from the database
        
        $sql = "SELECT `fname` FROM `sharex` WHERE `code` = ? LIMIT 1";
        $statement = $db->prepare($sql) or die("prepare()");
        $statement->bind_param("s", $_GET["i"]) or die("bind_param()");
        $statement->execute() or die("execute()");
        $statement->store_result();
        $statement->bind_result($result) or die("bind_result()");
        
        if (!$statement->fetch()) {
            die("Image not found");
        }
        else {
            $fname = $result;
        }
        
        $statement->close();
    }
    $db->close();

    $path = $folderPath.$fname;

    if (!file_exists($path)) {
        die("File not found");
    }
    else {
        $size = getimagesize($path);
        $f = fopen($path, "rb");

        if ($size and $f) {
            // Display image to the user
            
            header("Content-Type: ".$size["mime"]);
            header("Content-Length: ".filesize($path));
            fpassthru($f);
        }
        else {
            die("Failed to open file");
        }
    }
}
else {
    // -- Standard webpage --
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <!--<link rel="icon" href="favicon.png">-->  <!--196x196x-->
        <link rel="stylesheet" type="text/css" href="style.css">
        <script src="tabs.js"></script>
        <title>HQ Image Service</title>
    </head>
    <body>
        <div id="wrapper">
            <h1>HQ Image Service</h1>
            <a href="/images">
                <img id="ale" src="black-ale.png" alt="Beer">
            </a>
            <div id="tabHeader">
                <a href="#upload" class="tabLink active" onclick="changeTab(event, 'uploadTab')">
                    Upload
                </a>
                <a href="#sharex" class="tabLink" onclick="changeTab(event, 'sharexTab')">
                    Sharex
                </a>
            </div>
            <div id="uploadTab" class="tab active">
                <h2>Upload image</h2>
                <form id="uploadForm" method="post" enctype="multipart/form-data">
                    <input type="file" name="file"><br>
                    <input type="submit" value="Upload"><br>
                </form>
                <div id="formFooter">
                    Only images allowed.
                </div>
            </div>
            <div id="sharexTab" class="tab">
                <h2>Sharex uploader</h2>
                <textarea id="sharexSnippet" rows="8" cols="45" readonly>
{
  "Name": "i.miemala.com",
  "DestinationType": "None",
  "RequestType": "POST",
  "RequestURL": "http://i.miemala.com",
  "FileFormName": "file",
  "ResponseType": "RedirectionURL"
}
                </textarea>
            </div>
        </div>
    </body>
</html>

<?php
}
