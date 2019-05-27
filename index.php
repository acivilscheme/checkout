<?php
include "db_credentials.php";

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Products</title>

    <!-- [Bootstrap] Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- [Bootstrap] needs to be before other styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="mystyles.css">
    <link rel="stylesheet" href="styleProducts.css" />
    <link href="https://fonts.googleapis.com/css?family=Lato:400,400i|Fredericka+the+Great|Cabin|Encode+Sans+Condensed|Permanent+Marker" rel="stylesheet">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style type="text/css">
        form{
            text-align: left;
        }
    </style>
</head>
<body>
    <?php
        // ======================================================================
        // Initialize MySQLi server connection parameters
        // ======================================================================

        $username = $my_username;
        $password = $my_password;
        $dbName = $my_dbName;
        $tableName = $my_tableName;

        $servername = $my_servername;     

        // ======================================================================
        // Create connection with server
        // ======================================================================

        $conn = new mysqli($servername, $username, $password, $dbName);

        // ======================================================================
        // Check connection
        // ======================================================================

	    //My Console Log that outputs javascript
        function console_log($cStr)
        {
            echo '<script> console.log("PHP: ' . $cStr .'"); </script>';
        }
        
        if (!$conn)
        {
            die("Connection to Database $dbName failed: " . mysqli_connect_error() . "<br />");
        }

        //Cart code, creates cart, adds quantity if addtoCart button clicked, and clear cart
        if(!isset($_SESSION["cart"]))
        {
            $_SESSION["cart"] = array();
        }

        if(isset($_POST["addtoCart"]))
        {
            $newID = str_replace("album_id_", "", $_POST["id"]);

            if(!isset($_SESSION["cart"][$newID]))
                $_SESSION["cart"][$newID] = 0;

            $_SESSION["cart"][$newID] += $_POST["quantity"];
        }
        else if(isset($_POST["remove"]))
        {
            unset($_SESSION["cart"][$_POST["albumCartID"]]);
        }
        else if(isset($_POST["update"]))
        {
            $_SESSION["cart"][$_POST["albumCartID"]] = $_POST["albumCartQty"];
            if($_SESSION["cart"][$_POST["albumCartID"]] == 0)
                unset($_SESSION["cart"][$_POST["albumCartID"]]);
        }
        else if(isset($_POST["clearCart"]))
        {
            $_SESSION["cart"] = array();
            session_destroy();
        }
    ?>

    <div id="id_navSpace" class="p-2"></div>

    <!-- Shopping Cart -->
    <?php
        // Check if session exists
        if(is_array($_SESSION["cart"]))
        {
            echo "<div class='btn-group p-4' id='id_dropCart'>
                    <button type='button' class='btn btn-default btn-lg dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        Cart <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu w-100'><div>";

            foreach($_SESSION["cart"] as $index => $value)
            {   
                if($value > 0)
                {
                    $album_id = $index;
                    $queryproduct = mysqli_query($conn, "SELECT * FROM inventory WHERE sku='" . $album_id . "'");
                    if(!$queryproduct)
                    	die("Selecting from table inventory failed: " . mysqli_connect_error() . "<br />");

                    $getrow = mysqli_fetch_array($queryproduct);

                    echo "<form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post'>
                            <li class='row w-100 p-2' style='height: 100px;'>
                                <div class='col-4 h-100'>
                                    <img class='img-fluid-height' src='" . $getrow["album_cover_big"] ."' />
                                </div>
                                <div class='col-4'>
                                    <p class='c_nowrap'>" . $getrow["title"] . "</p>
                                    <p class='c_cartQty'>Qty: <input type='number' name='albumCartQty' min='0' max='99' value='" . $value . "' /></p>
                                    <p class='c_cartPrice'>$" . $getrow["price"] . "</p>
                                </div>
                                <div class='col-4 p-1'>
                                    <input type='text' name='albumCartID' value='" . $index . "' style='display: none;' />
                                    <p><button class='btn btn-primary btn-sm' type='submit' name='remove'>Remove</button></p>
                                    <p><button class='btn btn-primary btn-sm' type='submit' name='update'>Update</button></p>
                                </div>
                              </li>
                            </form>";     
                }
            }

            echo "</div><li role='separator' class='divider'></li>
                    <li class='row p-2'>
                        <div class='col-4'>
                            <p>Subtotal</p>
                            <p id='id_cartSubtotal'>
                                $99.99
                            </p>
                        </div>
                        <div class='col-4'>
                            <form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post'>
                                <button type='submit' name='clearCart' class='btn btn-primary btn-sm'>Clear Cart</button>
                            </form>
                        </div>
                        <div class='col-4'>
                            <form action='checkout.php' method='post'>
                                <button type='submit' name='checkout' class='btn btn-primary btn-sm'>Checkout</button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>";
        }
    ?>

    <div class="container-fluid w-100" style="border: 0px solid blue;">  
        <!-- Products -->
        <div class="row justify-content-center" style="min-height: 400px; border: 0px solid red;">
            <?php
                $sql = "SELECT * FROM $tableName";
                $result = mysqli_query($conn, $sql);
                if(!$result)
                    die("Selecting from table $tableName failed: " . mysqli_connect_error() . "<br />");
                if(mysqli_num_rows($result) > 0)
                {
                    $currentRow = 0;
                    while($row = mysqli_fetch_array($result))
                    {
                        if(($currentRow % 3) == 0 && ($currentRow != 0))
                        {
                            echo "</div><div class='row justify-content-center' style='min-height: 400px;'>";
                        }

                        echo "<div class='col-3 align-self-center m-3' id='album_id_" . $row["sku"] ."'>
                                <div class='c_album_container'>
                                    <figure class='w-75 mx-auto c_album p-3'>
                                        <img class='img-fluid' src='" . $row["album_cover_big"] . "' />
                                    </figure>
                                    <div class='c_aInfo_display w-75 mx-auto p-2 pt-0'>
                                    <div class='c_album_title c_nowrap'>" . $row["title"] . "</div>
                                    <div class='c_album_artist c_nowrap'>by " . $row["artist"] . "</div>
                                    <div class='c_album_price c_nowrap c_price'>$" . $row["price"] . "</div>
                                </div>
                                </div>
                                <div class='c_album_info'>
                                    <span class='c_album_genre'>" . $row["genre"] . "</span>
                                    <span class='c_album_year'>" . $row["year"] . "</span>
                                    <span class='c_album_quantity'>" . $row["numberInStock"] . "</span>
                                </div>
                            </div>";

                        $currentRow++;
                    }
                }
                
                mysqli_close($conn);
            ?>
    </div>
    
    <!-- Add to Cart Dialogue Box -->
    <div class="container-fluid text-left" id="id_pDescription" style="border: 0px solid purple; font-size: 5vw;">
        <div class="row justify-content-end pr-4" style="height: 10%; font-size: 5vh; color: black; border: 0px solid yellow;">
            <span id="id_close">&times;</span>
        </div>
        <div class="row p-4 justify-content-center" style="height: 90%; border: 0px solid purple;">
            <div class="col-7 align-self-center h-100" style="border: 0px solid purple;">
                <div class="h-100" id="id_pInfo" style="border: 0px solid blue; font-size: 5vw;">
                    <div class="c_nowrap h-25" style="font-size: 1em" id="id_pAlbumTitle">Album Title</div>
                    <div class="c_nowrap h-25" style="font-size: .8em;" id="id_pAlbumArtist">by Album Artist</div>
                    <div class="c_nowrap h-25 mt-5 mb-0" style="font-size: .8em;" id="id_pAlbumPrice">$12.99</div>
                    <div class="m-0 pt-2 h-25" style="line-height: 2px;">
                        <!-- Form for adding item to the cart -->
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="id_pForm">
                            <input type="text" name="id" value="id_pSKU" style="display: none;" />
                            <button type="submit" name="addtoCart" class="btn btn-primary h-auto" style="font-size: .5em; width: 35%; min-width: 75px;">Add to Cart</button>
                            <span style="font-size: .5em;">
                                Qty: <input type="number" name="quantity" min="0" max="99" value="1" />
                            </span>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-4 align-self-center h-100" style="border: 0px solid purple;">
                <figure class="mx-auto" style="height: 90%;">
                    <img style="max-height: 100%; max-width: 100%;" src="" />
                </figure>
            </div>
        </div>
    </div>
<!-- [Bootstrap] javascript plugins, jquery and popper.js for bootstrap -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    //When page open, set product description box to not display
    $("#id_pDescription").css({width: 0, height: 0, position: "fixed", opacity: 0});

    //Calculate prices in the cart
    addCartPrices();

    //Resize product description box on window resize
    $(window).resize(function(){
        if($("#id_pDescription").css("opacity") == "1")
        {
            //Resize box
            var w_width = $(window).innerWidth();
            var w_height = $(window).innerHeight();
            $("#id_pDescription").css({width: w_width, height: w_height * .75});

            //Recalculating left and top of position going back to when close box
            if($("#id_pDescription").data("albumClicked"))
            {
                var albumContainer = $("#id_pDescription").data("albumClicked");
                $("#id_pDescription").data("albumLeft", albumContainer.offset().left);
                $("#id_pDescription").data("albumTop", albumContainer.offset().top);
            }
        }
    });

    //When click album, open product description box
    //Need to move product to product description box when click
    $(".c_album").click(function(){
        //If previous albumClicked exists, show it when click another album
        if($("#id_pDescription").data("albumClicked"))
        {
            $("#id_pDescription").data("albumClicked").css({opacity: 1});
        }

        //Getting width and height of window
        var w_width = $(window).innerWidth();
        var w_height = $(window).innerHeight();
        var albumContainer = $(this).parent(".c_album_container");
        
        //Iniital position and size for product description box starts where album container's position is at
        var albumLeft = albumContainer.offset().left;
        var albumTop = albumContainer.offset().top - $(document).scrollTop();
        var albumCWidth = albumContainer.outerWidth();
        var albumCHeight = albumContainer.outerHeight();

        //Saving album clicked so can animate back to its position when product description box is closed
        $("#id_pDescription").data("albumClicked", albumContainer);
        $("#id_pDescription").data("albumLeft", albumLeft);
        $("#id_pDescription").data("albumTop", albumTop + $(document).scrollTop());

        //Set product description box position and size to album clicked and hide album clicked
        $("#id_pDescription").css({left: albumLeft, top: albumTop, width: albumCWidth, height: albumCHeight});
        albumContainer.css({opacity: 0});

        //Change Product Info
        var idClicked = $(this).parent(".c_album_container").parent().attr("id");
        
        $("#id_pAlbumTitle").text($("#"+idClicked).find(".c_album_title").text());
        $("#id_pAlbumArtist").text($("#"+idClicked).find(".c_album_artist").text());
        $("#id_pAlbumPrice").text($("#"+idClicked).find(".c_album_price").text());
        $("#id_pForm").children("input[name='id']").attr("value", idClicked);

        //Get image of album and put in description box before display box
        $("#id_pDescription").find("img").attr("src", $(this).find("img").attr("src"));
        $("#id_pDescription").css({opacity: 1, "pointer-events": "auto"});
        //Animate to window size and fade in contents
        $("#id_pDescription").animate({left: "0px", top: "25%", bottom: "0px", width: w_width, height: w_height * .75}, function(){
            //Set font size back to 1em/default font size
            $(this).find("#id_pAlbumTitle").css({"font-size": "1em"});
            $(this).find("#id_pAlbumArtist").css({"font-size": ".8m"});
            //Check if text is overflowing
            sWidth = $(this).find("#id_pAlbumTitle")[0].scrollWidth;
            iWidth = $(this).find("#id_pAlbumTitle").innerWidth();
            
            newSizeTitle = 1;
            newSizeArtist = .8;

            $(this).find(".row").animate({opacity: 1}, "slow");
        });
    });

    //When click X, close production description box
    $("#id_close").click(function(){
        //Get position and size of album animating back to
        var albumLeft = $("#id_pDescription").data("albumLeft");
        var albumTop = $("#id_pDescription").data("albumTop") - $(document).scrollTop();
        // console.log("Position[X Click]: (" + albumLeft + ", " + albumTop + ")");

        var albumCWidth = $("#id_pDescription").data("albumClicked").outerWidth();
        var albumCHeight = $("#id_pDescription").data("albumClicked").outerHeight();

        //Hide contents before move box
        $("#id_pDescription").find(".row").css({opacity: 0});

        //Moving box back to album position, hide box, and make album appear
        $("#id_pDescription").animate({width: albumCWidth, height: albumCHeight, position: "fixed", left: albumLeft, top: albumTop}, function(){
            $(this).css({opacity: 0, "pointer-events": "none"});
            $(this).data("albumClicked").css({opacity: 1});
        });
    });
});

//Calculate the cart total
function addCartPrices(){
    cartTotal = 0;

    $(".c_cartPrice").each(function(){
        itemPrice = $(this).text();
        itemPrice = itemPrice.replace("$", "");
        itemPrice = parseFloat(itemPrice);

        itemQty = $(this).siblings(".c_cartQty").children("input[name='albumCartQty']").attr("value");

        itemTotal = itemPrice * itemQty;

        cartTotal += itemTotal;
    });

    $("#id_cartSubtotal").text("$" + cartTotal.toFixed(2));
}
</script>

</body>
</html>