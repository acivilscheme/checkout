<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="mystyles.css">
    <link rel="stylesheet" href="styleProducts.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style type="text/css">
        .row {
            background-color: rgba(0, 0, 0, 0.8);
            padding-bottom: 50px;
        }
    </style>
</head>

<body style="background-color:rgb(255,255,255);">
    <?php
        include "db_credentials.php";
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

        if (!$conn)
        {
            die("Connection to Database $dbName failed: " . mysqli_connect_error() . "<br />");
        }
    

        //My Console Log that outputs javascript
        function console_log($cStr)
        {
            echo '<script> console.log("PHP: ' . $cStr .'"); </script>';
        }

        //Cart code, creates cart, adds quantity if addtoCart button clicked, and clear cart
        session_start();
        // console_log("Session Start");

        if(!isset($_SESSION["cart"]))
        {
            $_SESSION["cart"] = array();
        }

        if(isset($_POST["addtoCart"]))
        {
            // console_log("Add to Cart ID: " . $_POST["id"]);
            if(!isset($_SESSION["cart"][$_POST["id"]]))
                $_SESSION["cart"][$_POST["id"]] = 0;

            $_SESSION["cart"][$_POST["id"]] += $_POST["quantity"];
        }
        else if(isset($_POST["remove"]))
        {
            // console_log("Removed from Cart ID: " . $_POST["albumCartID"]);
            unset($_SESSION["cart"][$_POST["albumCartID"]]);
        }
        else if(isset($_POST["update"]))
        {
            // console_log("Updated ID: " . $_POST["albumCartID"]);

            $_SESSION["cart"][$_POST["albumCartID"]] = $_POST["albumCartQty"];

            if($_SESSION["cart"][$_POST["albumCartID"]] == 0)
                unset($_SESSION["cart"][$_POST["albumCartID"]]);
        }
        else if(isset($_POST["clearCart"]))
        {
            // console_log("Cart Clear");
            $_SESSION["cart"] = array();
            session_destroy();
        }
    ?>
  

    <a href="index.php"><button type="button" class="m-3 btn btn-primary btn-lg" aria-haspopup="true" aria-expanded="false">Back to Products</button></a>

    <!-- ******* Form ******* -->
    <div style="margin-bottom:100px;">
        <div class="container">
            <div class="row" style="border-style: solid; border-width: 1px;">
                <div class="col-md-12 col-md-offset-0">
                    <h2 style="text-align: center;"><img src="pics/checkout.png" style="width: 400px; max-width: 90vw;"></h1><hr>
                    <h1 style="text-align: left; padding-left: 40px;"><span style="text-decoration: underline;">Review Your Items</span> </h1></div>

                    <!-- Items in Cart -->
                    <div class='container-fluid w-100 p-4' style='border: 0px solid blue;'>
                        <ul class="list-group">
                        <?php

                            if(is_array($_SESSION["cart"]))
                            {
                                foreach($_SESSION["cart"] as $index => $value)
                                {
                                    if($value > 0)
                                    {
                                        $album_id = $index;
                                        $queryproduct = mysqli_query($conn, "SELECT * FROM inventory WHERE sku='" . $album_id . "'");
                                        $getrow = mysqli_fetch_array($queryproduct);

                                        echo "<form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post' class='w-75' style='height: 150px;'>
                                                <li class='row w-100 p-2 h-100 list-group-item'>
                                                    <div class='col-4 h-100'>
                                                        <img class='img-fluid-height' src='" . $getrow["album_cover_big"] . "' />
                                                    </div>
                                                    <div class='col-4'>
                                                        <h3 class='c_nowrap'>" . $getrow["title"] . "</h3>
                                                        <h4 class='c_cartQty'>Qty: <input type='number' name='albumCartQty' min='0' max='99' value='" . $value . "' /></h4>
                                                        <h4 class='c_cartPrice'>$" . $getrow["price"] . "</h4>
                                                    </div>
                                                    <div class='col-4 p-1'>
                                                        <input type='text' name='albumCartID' value='" . $index . "' style='display: none;' />
                                                        <p><button style='width: 100px;' class='btn btn-primary btn-lg' type='submit' name='remove'>Remove</button></p>
                                                        <p><button style='width: 100px;' class='btn btn-primary btn-lg' type='submit' name='update'>Update</button></p>
                                                    </div>
                                                </li>
                                            </form>";
                                    }
                                }
                            }
                        ?>
                        </ul>
                        <div class="row align-items-end" style="background-color: rgba(0,0,0,0);">
                            <div class="col-4" style="padding-left: 45px;">
                                <h3>Subtotal: <span id="id_cartSubtotal"></span></h3>
                                <h3>Tax: <span id="id_cartTax"></span></h3>
                                <h3>Total: <span id="id_cartTotal"></span></h3>
                            </div>
                        </div>
                    </div>
            </div>
            
        </div>
    </div>

    <!-- ******* JavaScript Links ******* -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/classie/1.0.1/classie.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            //Push the page down to make room for nav bar
            // $("#id_navSpace").height($("nav .navbar").outerHeight());

            //Calculate prices in the cart
            addCartPrices();
        });

        //Calculate the cart total
        function addCartPrices(){
            cartSubTotal = 0;

            $(".c_cartPrice").each(function(){
                itemPrice = $(this).text();
                itemPrice = itemPrice.replace("$", "");
                itemPrice = parseFloat(itemPrice);
                // console.log("Item Price Parse: " + itemPrice);
                itemQty = $(this).siblings(".c_cartQty").children("input[name='albumCartQty']").attr("value");
                // console.log("Item Qty: " + itemQty);
                itemTotal = itemPrice * itemQty;

                // console.log("Item Total: " + itemTotal);
                cartSubTotal += itemTotal;
            });
            
            // console.log("Cart Total: " + cartTotal);
            $("#id_cartSubtotal").text("$" + cartSubTotal.toFixed(2));

            salesTax = .0625;
            cartTax = cartSubTotal * salesTax;
            $("#id_cartTax").text("$" + cartTax.toFixed(2));

            cartTotal = cartSubTotal + cartTax;
            $("#id_cartTotal").text("$" + cartTotal.toFixed(2));
        }
    </script>

</body>

</html>
