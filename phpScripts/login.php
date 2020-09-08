<?php
function login($id, $name) {
    // Create connection
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 
    // Check connection
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $userID = mysqli_real_escape_string($link, $id);
    $userName = mysqli_real_escape_string($link, $name);

    // log host information
    $logHost = '<script>' . $logHost . '</script>';
    echo $logHost;
    // Attempt insert query execution
    $sql = "INSERT INTO User (ID, Name) VALUES ('$userID', '$userName')";

    mysqli_query($link, $sql);
    //     echo "console.log('Records inserted successfully.')";
    // } else{
    //     echo "console.log('already in DB')";
    // }

    // Close connection
    mysqli_close($link);
}
?>