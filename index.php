<?php
@ob_start();
session_start();
?>
<?
//LINE 345
    //requires the steamauth.php file for login functions and to get user information
    require 'steamauth/steamauth.php';
    require 'steamauth/userInfo.php';
    require 'phpScripts/login.php';
    require 'phpScripts/insert.php';

    //checks if the user is logged in
    if(isset($_SESSION['steamid'])) {
        $id = $_SESSION['steamid'];
    } else {
        #Not logged in
    }
?>
<html lang="en">
<head>
         <!-- CSS and JQuery here -->
         <link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet">
         <link rel="stylesheet" href="css/index.css?version=0">
         <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
         <!-- <script src="js/index.js"></script> -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TierFortress</title>
</head>
<body>

    <!-- ----------------------------------- -->
    <!-- --------------NavBar--------------- -->
    <!-- ----------------------------------- -->

    <div id="header" >
        <img src="images/tierFortressLogoWhite.png" id="logo">
    <!-- If the user is logged in with steam display this -->
    <? if (isset($_SESSION['steamid'])) {
        login($_SESSION['steamid'], $steamprofile['personaname'])

        ?>
        <a class="userInfo">
            <img class="profilePic" src="<?=$steamprofile['avatar']?>"><b id="profileName"><?=$steamprofile['personaname']?></b>
        </a>
    
    <!-- If the user is not logged in with steam display this (login button) -->
    <?} else {?>
        <? echo loginbutton('rectangle');?>
    <?} ?>

    </div>
    <!-- ----------------------------------- -->
    <!-- ------------filters---------------- -->
    <!-- ----------------------------------- -->
    <div id="classes">
        <input type="image" src="images/allClass.png" name="saveForm" class="classIcon submit" id="allClass" onclick=classFilter("all")  />
        <input type="image" src="images/scout.jpg" name="saveForm" class="classIcon submit" id="scout" onclick=classFilter("scout") />
        <input type="image" src="images/soldier.jpg" name="saveForm" class="classIcon submit" id="soldier" onclick=classFilter("soldier") />
        <input type="image" src="images/pyro.jpg" name="saveForm" class="classIcon submit" id="pyro" onclick=classFilter("pyro") />
        <input type="image" src="images/demoman.jpg" name="saveForm" class="classIcon submit" id="demoman" onclick=classFilter("demoman") />
        <input type="image" src="images/heavy.jpg" name="saveForm" class="classIcon submit" id="heavy" onclick=classFilter("heavy") />
        <input type="image" src="images/engineer.jpg" name="saveForm" class="classIcon submit" id="engineer" onclick=classFilter("engineer") />
        <input type="image" src="images/medic.jpg" name="saveForm" class="classIcon submit" id="medic" onclick=classFilter("medic") />
        <input type="image" src="images/sniper.jpg" name="saveForm" class="classIcon submit" id="sniper" onclick=classFilter("sniper") />
        <input type="image" src="images/spy.jpg" name="saveForm" class="classIcon submit" id="spy" onclick=classFilter("spy") />

    </div>
    <div id="slotAndOrder">
        <div id="slots">
            <input type="image" src="images/allSlots.png" name="saveForm" class="slotIcon submit" id="allSlot" onclick=slotFilter("all") />
            <input type="image" src="items/Rocket_Launcher.png" name="saveForm" class="slotIcon submit" id="primary" onclick=slotFilter("primary") />
            <input type="image" src="items/Pistol.png" name="saveForm" class="slotIcon submit" id="secondary" onclick=slotFilter("secondary") />
            <input type="image" src="items/Fire_Axe.png" name="saveForm" class="slotIcon submit" id="melee" onclick=slotFilter("melee") />
            <input type="image" src="items/Invis_Watch.png" name="saveForm" class="slotIcon submit" id="other" onclick=slotFilter("other") />
        </div>
        <div id="ordering">
            <input type="image" src="images/ratingHigh-Low.png" name="saveForm" class="orderIcon submit" id="order1" onclick=orderFilter(1) />
            <input type="image" src="images/ratingLow-High.png" name="saveForm" class="orderIcon submit" id="order2" onclick=orderFilter(2) />
            <input type="image" src="images/nameHigh-Low.png" name="saveForm" class="orderIcon submit" id="order3" onclick=orderFilter(3) />
            <input type="image" src="images/nameLow-High.png" name="saveForm" class="orderIcon submit" id="order4" onclick=orderFilter(4) />
        </div>
    </div>
    <!-- ----------------------------------- -->
    <!-- --------------Body----------------- -->
    <!-- ----------------------------------- -->
    <!-- page number buttons here -->
    <div id='pageNumContainer'>
    </div>

    <!-- loading icon here -->
    <div id="loading">
        <img src="images/loading.gif">
    </div>

    <!-- items generated from DB here -->
    <div id="pageBodyContainer">
        <ul id='pageBody'>
        </ul>
    </div>
    <div id="pageNumContainerBottom">
        
    </div>
    <!-- SCRIPTS -->
    <script>
        //Necessary global variables

        //array of variables which are used to generate database queries
        //0. All classes 1. All equip slots 2. sort by rating ('r') 3. Filter by DESC (high to low) 4. Page #1
        let filterArray = ["all", "all", "r", "DESC, i ASC", 0]; 
        //used to timeout ajax calls
        let abortCalls = false;
        //used to block additional button presses while one has already be selected
        let filtersBlocked = false;
        //global variable that stores the number of pages to be displayed
        let pageNumber;
        //Stores the user's current page
        let currentPage = 0;
        //stores all the value of the class filter (used for detemrining selections and styling of the filter)
        let classArr = ["allClass", "scout", "soldier", "pyro", "demoman", "heavy", "engineer", "medic", "sniper", "spy"];
        //stores all the value of the slot filter (used for detemrining selections and styling of the filter)
        let slotArr = ["allSlot", "primary", "secondary", "melee", "other"];
        //stores all the value of the order filter (used for detemrining selections and styling of the filter)
        let orderArr = ["order1", "order2", "order3", "order4"];

        //highlights the current class filter selection
        function classImageSelection(x) {
            if(x == "all") {
                for(let j = 0; j < classArr.length; j++) {
                    $("#"+classArr[j]).css({"-moz-filter": "grayscale(0%)", "-o-filter": "grayscale(0%)", "-ms-filter": "grayscale(0%)", "filter": "grayscale(0%)"});
                }
            } else {
                for(let i = 0; i < classArr.length; i++) {
                    if(x != classArr[i]) {
                        $("#"+classArr[i]).css({"-moz-filter": "grayscale(100%)", "-o-filter": "grayscale(100%)", "-ms-filter": "grayscale(100%)", "filter": "grayscale(100%)"});
                    } else {
                        $("#"+classArr[i]).css({"-moz-filter": "grayscale(0%)", "-o-filter": "grayscale(0%)", "-ms-filter": "grayscale(0%)", "filter": "grayscale(0%)"});
                    }
                }
            }
        };
        
        //highlights the current slot filter selection
        function slotImageSelection(x) {
            if(x == "all") {
                for(let j = 0; j < slotArr.length; j++) {
                    $("#"+slotArr[j]).css({"-moz-filter": "grayscale(0%)", "-o-filter": "grayscale(0%)", "-ms-filter": "grayscale(0%)", "filter": "grayscale(0%)"});
                }
            } else {
                for(let i = 0; i < slotArr.length; i++) {
                    if(x != slotArr[i]) {
                        $("#"+slotArr[i]).css({"-moz-filter": "grayscale(100%)", "-o-filter": "grayscale(100%)", "-ms-filter": "grayscale(100%)", "filter": "grayscale(100%)"});
                    } else {
                        $("#"+slotArr[i]).css({"-moz-filter": "grayscale(0%)", "-o-filter": "grayscale(0%)", "-ms-filter": "grayscale(0%)", "filter": "grayscale(0%)"});
                    }
                }
            }
        };

        //highlights the current order filter selection
        function orderImageSelection(x) {
            for(let i = 0; i < orderArr.length; i++) {
                if(x != orderArr[i]) {
                    $("#"+orderArr[i]).css({"-moz-filter": "grayscale(100%)", "-o-filter": "grayscale(100%)", "-ms-filter": "grayscale(100%)", "filter": "grayscale(100%)"});
                } else {
                    $("#"+orderArr[i]).css({"-moz-filter": "grayscale(0%)", "-o-filter": "grayscale(0%)", "-ms-filter": "grayscale(0%)", "filter": "grayscale(0%)"});
                }
            }
        };

        //Clears the page of items and filters by class. Called when a classIcon input submission occurs 
        function classFilter(className) {
            //If filter selection has been updated and button presses are permitted clear the button elements and query the database with the new parameters
            if (filterArray[0] != className && filtersBlocked == false) {
                filterArray[0] = className;
                abortCalls = true;
                filtersBlocked = true;
                $("input")[0].disabled = true;
                filterArray[4] = 0;
                $('#pageBody').children().remove();
                $('#pageNumContainer').children().remove();
                //let ajax calls finish then clear the page of values
                setTimeout(function() {
                    $('#pageBody').children().remove();
                    $('#pageNumContainer').children().remove();
                    abortCalls = false; 
                    getItems()
                    $("input")[0].disabled = false;
                }, 500);
                classImageSelection(className);
                //allow new filter button presses
                setTimeout(function(){
                    filtersBlocked = false;
                }, 2000);
            }
        };

        //Clears the page of items and filters by equip slot. Called when a slotIcon input submission occurs 
        function slotFilter(slotName) {
            //If filter selection has been updated and button presses are permitted clear the button elements and query the database with the new parameters
            if (filterArray[1] != slotName && filtersBlocked == false) {
                filterArray[1] = slotName;
                abortCalls = true;
                filtersBlocked = true;
                $("input")[0].disabled = true;
                filterArray[4] = 0;
                $('#pageBody').children().remove();
                $('#pageNumContainer').children().remove();
                //let ajax calls finish then clear the page of values
                setTimeout(function() {
                    abortCalls = false; 
                    $('#pageBody').children().remove();
                    $('#pageNumContainer').children().remove();
                    getItems();
                    $("input")[0].disabled = false;
                }, 500);
                slotImageSelection(slotName);
            }
            //allow new filter button presses
            setTimeout(function(){
                    filtersBlocked = false;
            }, 2000);
        };

        //Clears the page of items and filters by ASC or DESC and Name or Rating. Called when a orderIcon input submission occurs 
        function orderFilter(orderNum) {
            switch (orderNum) {
                case 1:
                    orderName = 'r';
                    orderDir = 'DESC, i ASC';
                    break;
                case 2:
                    orderName = 'r';
                    orderDir = 'ASC, i ASC';
                    break;
                case 3:
                    orderName = 'i';
                    orderDir = 'ASC';
                    break;
                case 4:
                    orderName = 'i';
                    orderDir = 'DESC';
                    break;
                default:
                    orderName = 'r';
                    orderDir = 'DESC';
                    break;
            }
            if (filterArray[2] != orderName || filterArray[3] != orderDir) {
                filterCheck(orderName, orderDir, 0, true, orderNum);
            }
        };

        function filterCheck(a, b, c, d, orderNum){
            //If filter selection has been updated and button presses are permitted clear the button elements and query the database with the new parameters
            if ((filterArray[4] != c || d) && filtersBlocked == false) {
                abortCalls = true;
                filtersBlocked = true;
                filterArray[2] = a;
                filterArray[3] = b;
                filterArray[4] = c;
                $('#pageBody').children().remove();
                $('#pageNumContainer').children().remove();
                //let ajax calls finish then clear the page of values
                setTimeout(function() {
                    getItems();
                    abortCalls = false; 
                }, 500);
                orderImageSelection("order"+orderNum);
                //allow new filter button presses
                setTimeout(function(){
                    filtersBlocked = false;
                }, 2000);
            }
        };


        //creates page navigation functionality
        function defPageButton() {
            $('.pageNum').click(function() {
                currentPage = parseInt(this.innerHTML) * 12 - 12;
                filterCheck(filterArray[2], filterArray[3], currentPage, false);
            })        
        };

        // Main function 
        // Takes filterArray values which represent which filters will be applied through a DB query
        // Passes getItemInfo() a filtered array of table names
        async function getItems() {
            getPageNums();
            let queryNum;
            //queryNum reflects which filters have been applied by the user to determine which query to execute when generating page elements
            if(filterArray[0] == "all" && filterArray[1] == "all") {
                queryNum = 1;
            } else if(filterArray[0] != "all" && filterArray[1] == "all") {
                queryNum = 2;
            } else if(filterArray[0] == "all" && filterArray[1] != "all") {
                queryNum = 3;
            } else if(filterArray[0] != "all" && filterArray[1] != "all") {
                queryNum = 4;
            }
            //returns a filtered array of table names
            result = await $.ajax({
                type: "POST",
                url: 'phpScripts/insert.php',
                dataType: 'json',
                data: {functionname: 'getItems', queryType: queryNum, class: filterArray[0], slot: filterArray[1], orderName:  filterArray[2], orderDir: filterArray[3], page: filterArray[4]},
            });
            getItemInfo(result);
        };

        //Gets the total number of pages
        async function getPageNums() {
            let queryNum;
            let className = filterArray[0];
            let slotName = filterArray[1];
            if(filterArray[0] == "all" && filterArray[1] == "all") {
                queryNum = 1;
            } else if(filterArray[0] != "all" && filterArray[1] == "all") {
                queryNum = 2;
            } else if(filterArray[0] == "all" && filterArray[1] != "all") {
                queryNum = 3;
            } else if(filterArray[0] != "all" && filterArray[1] != "all") {
                queryNum = 4;
            }
            //returns the number of items
            itemNums = await $.ajax({
                type: "POST",
                url: 'phpScripts/insert.php',
                dataType: 'json',
                data: {functionname: 'getPageNums', queryType: queryNum, class: className, slot: slotName},
            });
            //calculates the number of pages based on the number of items being queried (12 items displayed per page)
            pageNumber = Math.ceil(itemNums / 12);
            //Generates and styles page number buttons
            for(let i = 1; i < pageNumber + 1; i++) {
                $('#pageNumContainer').append('<button class="pageNum" id="page' + i + '">' + i + '</button>');
            }
            $('#page' + Math.floor((filterArray[4] / 12) + 1)).css('background-color', '#102e6e');
            $('#page' + Math.floor((filterArray[4] / 12) + 1)).css('color', 'white');
            $("#loading").css("display", "flex")
            //defines page button function once the buttons have been generated
            defPageButton();
        };

        //Generates page elements based on the filtered array of item names passed by getItems()
        async function getItemInfo(itemArr) {
            for(let i = 0; i < itemArr.length; i++) {
                let itemReference = result[i];
                //returns an array of the item name[0], it's equip slot[1], and the TF2 class it belongs to[2] 
                itemInfo = await $.ajax({
                    type: "POST",
                    url: 'phpScripts/insert.php',
                    dataType: 'json',
                    data: {functionname: 'getItemInfo', itemName: itemReference}
                });
                if(!abortCalls) {
                    //replaces placeholder chars with their proper char as the item name could not be properly stored as a SQL table name
                    let itemFixed = itemInfo[0].replace(/9/g, "\'").replace(/8/g, "!").replace(/7/g, "-").replace(/4/g, ".").replace(/_/g, " ").replace(/3/g, "(").replace(/2/g, ")");
                    //generates the item elements to be displayed on the website page body
                    $("#pageBody").append('<li class="items" style="display:none">'
                    + '<div class="itemContainer ' + itemInfo[0] + 'Container ' + itemInfo[1] + ' ' + itemInfo[2] + '">'
                    + '<a class="itemNameContainer"><p class="itemName">' + itemFixed + '</p></a>'
                    + '<a class="itemBodyContainer">'
                    + '<img class="itemImage" src="items/' + itemInfo[0] + '.png" alt="Image Not Found">'
                    + '<p class="yourRating">Your Rating:</p>'
                    + '<div class="buttonContainer">'
                    + '<button class="1 ' + itemInfo[0] + ' item" style="">1</button>'
                    + '<button class="2 ' + itemInfo[0] + '">2</button>'
                    + '<button class="3 ' + itemInfo[0] + '">3</button>'
                    + '<button class="4 ' + itemInfo[0] + '">4</button>'
                    + '<button class="5 ' + itemInfo[0] + '">5</button>'
                    + '</div>'
                    + '<p id="' + itemInfo[0] + 'Average"></p>'
                    + '</a>'
                    + '</div>'
                    +'</li>');
                    //gets and displays the user's rating for the newly generated item
                    getRatings(itemInfo[0]);
                    //gets and displays the mean rating for the newly generated item
                    getAverageRatings(itemInfo[0]);
                } else {
                    return;
                }
            }
            //generates button functionality
            defButton();
        };

        //Generates button functionality once the elements have been generated from getItemInfo()
        function defButton() {
            $("#loading").css("display", "none")
            $(".items").css("display", "inline");
            $(".buttonContainer > button").click(function () {
            //variable declaration for id, rating, and the table name values
            let rating = this.classList[0];
            let tableName = this.classList[1];
            let id = "<?php echo $_SESSION['steamid']; ?>";

            //Posts id, rating and table name to the insert.php file (which writes it to the db)
            $.ajax({
                type: "POST",
                url: 'phpScripts/insert.php',
                dataType: 'json',
                data: { functionname: 'insert', userID: id, userRating: rating, table: tableName },
                success: function () {
                }
            });
            //colors the button elements according to the user's rating
            let currentTable = document.getElementsByClassName(tableName);
                for(let i = 0; i < currentTable.length; i++) {
                    if (rating >= currentTable[i].classList[0]) {
                        currentTable[i].style.backgroundColor = 'yellow';
                    } else {
                        currentTable[i].style.backgroundColor = 'white';
                    }
                }
            });
        };

        //gets the users ratings for the item from the database and displays them on the page
        async function getRatings(itemName) {
            let tableName = itemName;
            let id = "<?php echo $_SESSION['steamid']; ?>"
            let result = 0;
            result = await $.ajax({
                type: "POST",
                url: 'phpScripts/insert.php',
                dataType: 'json',
                data: {functionname: 'getRating', table: tableName, userID: id},

            });
            //colors the rating buttons appropriately if ajax calls are not being stopped
            if(!abortCalls) {
                let currentTable = document.getElementsByClassName(tableName);
                for(let i = 0; i < currentTable.length; i++) {
                    if (result >= currentTable[i].classList[0]) {
                        currentTable[i].style.backgroundColor = 'yellow';
                    } else {
                        currentTable[i].style.backgroundColor = 'white';
                    }
                }
            } else {
                return;
            }
        };

        //Gets the average rating of each item if AJAX calls are permitted
        async function getAverageRatings(itemName) {   
            let result = await $.ajax({
                type: "POST",
                url: 'phpScripts/insert.php',
                dataType: 'json',
                data: {functionname: 'getAverageRating', table: itemName},

            });
            if(!abortCalls) {
                let currentTable = document.getElementsByClassName(itemName);
                document.getElementById(itemName + 'Average').innerHTML = "Average: " + result;
            } else {
                return;
            }
        };

        //Calls getItems on document ready. 
        //Page loading goes in this order: getItems() --> getItemInfo() --> getRatings() + getAverageRatings() --> defButton() 
        $(document).ready(function() {
            getItems();
            orderImageSelection('order1');
        });
    </script>
</body>
</html>