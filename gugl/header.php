<?php

// this file is the header for the site.

/*
    the adminPage global var included to ensure correct paths throughout the site, 
    due to difference in directories between the admin and user sides
*/
global $dminPage;
// calculate the prefix for the main header depending on if its an admin page
$prefix = $adminPage ? "../" : "";

$adminHeaderPrefix = !$adminPage ? "admin/" : "";
echo "<div class='page-header'>
<div class='container'>";

// if the admin is logged in, show the admin header
if ($adminLoggedIn){
    echo "<div class='row'>
    <a href='{$adminHeaderPrefix}admin_dashboard.php'>
    <div class='col-md-12 col-sm-12 col-xs-12 admin-header'>
        <h2 class='text-center'>ADMIN</h2>";
        $logoutLink = "admin_logout.php";
        // add the prefix if the page isnt an admin page
        if (!$adminPage){
            $logoutLink = "admin/".$logoutLink;
        }
        echo "<h3 class='text-center'><a href='{$logoutLink}'>LOG OUT</a></h3>
    </div></a>
</div>";
}



echo "<div class='row' id='header'>
                    <div class='col-md-4'>
                        <h1 id='maintitle'><a href='{$prefix}index.php' id='headlink'>GÜGL</a></h1>
                    </div>
                    <div class='col-md-6 navcol'>
                        <nav id='mainnav'>
                            <div class='row'>
                                <div class='col-md-8 col-sm-12'>
                                    <ul class='nav nav-pills nav-justified'>
                                        <li><a href='{$prefix}index.php' class='active'>Home</a></li>
                                        <li class='dropdown' role='navigation'>
                                            <a href='#' class='dropdown-toggle' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Apps <span class='caret'></span></a>
                                            <ul class='dropdown-menu'>
                                                <li><a href='{$prefix}search.php'>Search</a></li>
                                                <li role='separator' class='divider'></li>
                                                <li><a href='#'>Popular</a></li>
                                                <li role='separator' class='divider'></li>
                                                <li class='dropdown-header'>Categories</a>
                                                </li>
                                                <li><a href='{$prefix}genre.php?genreid=1'>Social Media</a></li>
                                                <li><a href='{$prefix}genre.php?genreid=2'>Entertainment</a></li>
                                                <li><a href='{$prefix}genre.php?genreid=4'>Office</a></li>
                                            </ul>
                                        </li>
                                        <li class='dropdown' role='navigation'>
                                            <a href='#' class='dropdown-toggle' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Account <span class='caret'></span></a>
                                            <ul class='dropdown-menu'>";

                                            // this menu changes if a regular user is logged in
                                            if ($loggedIn){
                                                echo "<li><a href='view_account.php'>edit Account</a></li>
                                                    <li role='separator' class='divider'></li>
                                                    <li><a href='#'>support</a></li>
                                                    <li role='separator' class='divider'></li>
                                                    <li><a href='{$prefix}logout.php'>log out</a></li>

                                            </ul>";
                                            } else {
                                                echo "<li><a href='{$prefix}login.php'>log in</a></li>
                                                    <li role='separator' class='divider'></li>
                                                    <li><a href='#'>support</a></li>
                                            </ul>";
                                            }
                                            echo "</li>
                                            </ul>
                                        </div>
        
                                        <div class='col-md-4 col-sm-12 searchcol text-centre'>
                                        <div class='text-centre'>
                                                <form class='navbar-form form-inline navbar' method='get' action='{$prefix}search.php'>
                                                    
                                                        <div class='form-group has-feedback has-search'>
                                                        <div class='input-group input-group'>
                                                            <input type='text' class='form-control' name='search' placeholder='Search'>
                                                            <span class='input-group-btn'>
                                                                <button type='submit' value='search-submit' name='search-submit' class='btn btn-default'><span class='glyphicon glyphicon-search'></span></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                            </div>
        
                        </div>
                        
                    </div>
                    <div class='row'>";
                    // shows a little prompt if the user is not logged in, otherwise gives
                    // a link to their account page
                    if($loggedIn){
                        echo "
                            <div class='col-md-3 pull-right account-prompt'><a href='{$prefix}view_account.php'>welcome, {$results['FirstName']} <span class='glyphicon glyphicon-user'></span></a>
                        ";
                    } else {
                        echo "

                            <div class='col-md-3 pull-right account-prompt'><a href='{$prefix}login.php'>log in/sign up</a>

                        ";
                    }

                    // if a basket is set, details are shown here
                    if(isset($_SESSION['basket'])){
                        $basketCount = count($_SESSION['basket']['contents']);
                    } else {
                        $basketCount = 0;
                    }
                    echo  "<span>|</span>  <a href='basket.php'><span class='glyphicon glyphicon-shopping-cart'></span><span class='badge' id='cart-count'>{$basketCount}</span></a></div>
                    </div>
                    </div>";
                    

?>