<?
function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}


if ($_POST['functionname'] == 'getAverageRating') {
    $table = $_POST['table'];
    // Create connection to DB
    debug_to_console( "Test" );
    $link = new mysqli("localhost", "//db write account name//", "//password//", "//db name//"); 

    // Check connection
    if($link->connect_error){
        die("ERROR: Could not connect. " . $conn->connect_error);
    }
    $sql = "SELECT AVG (rating) FROM $table";
    if ($avgResult = mysqli_query($link, $sql)) {
        echo $avgResult;
    }
    $link->close();
}
?>