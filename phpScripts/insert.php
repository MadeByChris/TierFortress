<?php
//Gets the names of tables from the database ordered by highest rating and returns them in an array
if ($_POST['functionname'] == 'getItems') {
    $itemArray = array();
    // Create connection to DB
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 
    $queryType = $_POST['queryType'];
    $class = $_POST['class'];
    $slot = $_POST['slot'];
    $orderBy = $_POST['orderName'];
    $orderDir = $_POST['orderDir'];
    $page = $_POST['page'];
    switch ($queryType) {
        //no filter has been applied
        case 1:
            $sql = "Call stored procedure here";
            break;
        //class filter has been applied
        case 2:
            $sql = "Call stored procedure here";
            break;
        //slot filer has been applied
        case 3:
            $sql = "Call stored procedure here";
            break;
        //class and slot filter has been applied
        case 4:
            $sql = "Call stored procedure here";
            break;
        }
    // Check connection
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    //Iterates through the query result and pushes the name of each item into an array
    $result = $link->query($sql) or die($conn->error);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            array_push($itemArray, $row['i']);
        }
    }
    echo json_encode($itemArray);
    
    // Close connection
    mysqli_close($link);
}

//Gets the count of items with filters applied, which is used to calculate the number of pages to generate
if ($_POST['functionname'] == 'getPageNums') {
    $queryType = $_POST['queryType'];
    $class = $_POST['class'];
    $slot = $_POST['slot'];
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 

    switch ($queryType) {
        case 1:
            $sql = "SELECT COUNT(Name) AS 'c' FROM Items";
            break;
        case 2:
            $sql = "SELECT COUNT(Name) AS 'c' FROM `Items` WHERE Items.class LIKE '$class'";
            break;
        case 3:
            $sql = "SELECT COUNT(Name) AS 'c' FROM `Items` WHERE Items.slot LIKE '$slot'";
            break;
        case 4:
            $sql = "SELECT COUNT(Name) AS 'c' FROM `Items` WHERE Items.class LIKE '$class' AND Items.slot LIKE '$slot'";
            break;
        default:
            $sql = "SELECT COUNT(NAME) AS 'c' FROM `Items`";
            break;
    }
    // Check connection
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    
    $result = mysqli_query($link, $sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo $row['c'];
        }
    }
}
//Is called after the average rating of items occurs to generate class name information for the HTML
if ($_POST['functionname'] == 'getItemInfo') {
    //variable declaration
    $itemName = $_POST['itemName'];
    $itemDetails = array();
    // Create connection to DB
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 
    // Check connection
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    //selects the values of the item that has been generated in getItems()
    $sql = "SELECT * FROM Items WHERE `Name` LIKE '$itemName'";
    $result = $link->query($sql) or die($link->error);
    $index = 0;
    while($row = $result->fetch_assoc()) {
        array_push($itemDetails, $row['Name']);
        array_push($itemDetails, $row['slot']);
        array_push($itemDetails, $row['class']);

    }
    echo json_encode($itemDetails);
    // Close connection
    mysqli_close($link);
}

//Writes to the database on button press
//triggers when the ajax post with the correct 'functionName' is submitted
if ($_POST['functionname'] == 'insert') {
    //variable declaration
    $table = $_POST['table'];
    $rating = $_POST['userRating'];
    $id = $_POST['userID'];
    // Create connection to DB
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 
    // Check connection
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    $userID = mysqli_real_escape_string($link, $id);
    $userRating = mysqli_real_escape_string($link, $rating);
    //Inserts the user rating and id into the DB corresponding to the button they pressed, or updates the rating if the id already exists
    $sql = "INSERT INTO $table (id, r) VALUES($userID, $userRating) ON DUPLICATE KEY UPDATE r = $userRating";
    mysqli_query($link, $sql);
    // Close connection
    mysqli_close($link);
}

//Gets the user's rating when the element is loaded
//triggers when the ajax post with the correct 'functionName' is submitted
if ($_POST['functionname'] == 'getRating') {
    $table = $_POST['table'];
    $id = $_POST['userID'];
    // Create connection to DB
    $link = mysqli_connect("localhost", "//db write account name//", "//password//", "//db name//"); 
    // Check connection
    if($link->connect_error){
        die("ERROR: Could not connect. " . $conn->connect_error);
    }
    $userID = mysqli_real_escape_string($link, $id);
    $sql = "SELECT r FROM $table WHERE id = $userID";
    //echoes the user's rating if it exists
    $result = mysqli_query($link, $sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo $row['r'];
        }
    }
    $link->close();
}

//Gets the average rating when the element is loaded
//triggers when the ajax post with the correct 'functionName' is submitted
if ($_POST['functionname'] == 'getAverageRating') {
    $table = $_POST['table'];
    // Create connection to DB
    $link = new mysqli("localhost", "//db write account name//", "//password//", "//db name//"); 
    // Check connection
    if($link->connect_error){
        die("ERROR: Could not connect. " . $conn->connect_error);
    }
    //Runs the query and echoes the average rating of the item
    $sql = "SELECT ROUND(AVG(r), 2) AS 'rating' FROM $table";
    $result = mysqli_query($link, $sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo $row['rating'];
        }
    }
    $link->close();
};

//The $_POST['functionname'] evaluation should be updated to a switch statement when I have time
?>
