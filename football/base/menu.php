<?php
// This is temporary, but maybe it's not such a bad idea
require_once 'utils/start.php';
if (!isset($title)) {
    $title = 'WMFFL';
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title><?= $title; ?></title>
    <link rel="icon" href="/images/logo.png" type="image/png"/>
    <link rel="SHORTCUT ICON" href="/images/logo.png"/>

    <?php
    // Include any Javascript
    print '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>';
    if (isset($javascriptList)) {
        foreach ($javascriptList as $sheet) {
            print "<script src=\"$sheet\"></script>";
        }
    }
    print '<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>';

    // If no cssList then add it, otherwise add core.css
    if (isset($cssList)) {
        array_unshift($cssList, '/base/css/core.css?v12');
//        array_unshift($cssList, "/base/vendor/css/bootstrap.min.css");
        array_unshift($cssList, 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css');
    } else {
//        $cssList = array("/base/vendor/css/bootstrap.min.css", "/base/css/core.css");
        $cssList = array('https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css', '/base/css/core.css');
    }

    // Print out the css
    foreach ($cssList as $sheet) {
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\"></script>";
    }
    ?>

    <script language="JavaScript">
        window.addEventListener("load", function() {
            let foo = Cookies.get('showlogin');
            if (foo === "1") {
                $("#loginModal").modal('show');
            }
            Cookies.remove('showlogin');
        });

        function submitContactForm() {
            var url = "/login/login";

            $.ajax({
                type: "POST",
                url: url,
                data: $("form.form-signin").serialize(),
                success: function(data)
                {
                    // if request successful
                    if (data.startsWith('err')) {
                        let msg = data.substring(data.indexOf('-') + 1).trim();
                        $("#loginAlert")[0].textContent = msg;
                        $("#loginAlert").show();
                    } else {
                        $("#loginAlert").hide();
                        location.reload();
                    }
                },
                error: function(data) {
                    // if request unsuccessful
                    alert('Failure');
                },
            });
            // e.preventDefault(); // avoid actually submition of form
        };

        // $("#loginModal").submit(function(e) {
        //    alert("Submitted");
        // });

    </script>

</head>

<!-- Begin Menu.html -->
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-2">
    <a class="navbar-brand" href="/"><img src="/images/logo.png"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active"> <a class="nav-link pl-2" href="/">Home <span class="sr-only">(current)</span></a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/article/list">News</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/activate/activations">Activations</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/teams/">Teams</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/stats/leaders">Stats</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/history/2022Season/schedule">Schedule</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/history/2022Season/standings#">Standings</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/transactions/transactions">Transactions</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/rules/">Rules</a> </li>
            <li class="nav-item"> <a class="nav-link pl-2" href="/history/">History</a> </li>
        </ul>
        <?php
        if ($isin) {
            ?>
            <button class="btn btn-wmffl my-2 my-sm-0" data-toggle="modal" data-target="#profileModal"><?= $fullname ?></button>
            <?php
        } else {
            ?>
            <button class="btn btn-wmffl my-2 my-sm-0" data-toggle="modal" data-target="#loginModal">Log In</button>
        <?php
        }
        ?>
    </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-signin" method="post">
            <div class="modal-header">
                <h1 class="modal-title mt-0" id="loginModalLabel">Log In</h1>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="modal-body">
                    <div class="alert alert-danger collapse" id="loginAlert" role="alert">Some Text</div>
                    <div class="username-container">
                        <label for="username" class="sr-only">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username" required
                               autofocus>
                    </div>
                    <div class="password-container mt-3">
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>
            <div class="modal-footer">
                <button type="button" onclick="submitContactForm();" class="btn btn-lg btn-wmffl btn-block">Log In</button>
            </div>
                <div class="mx-auto my-2 text-center"><a href="/login/forgotpassword">Forgot Password?</a></div>
            </form>
        </div>
    </div>
</div>


<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title mt-0" id="loginModalLabel">Profile <?= $fullname ?></h1>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a class="btn btn-lg btn-wmffl btn-block" href="/login/newpassword" role="button">Change Password</a>
                </div>
            <?php
            if ($isin && $teamnum == 2) {
                ?>
                <div class="modal-body">
                    <a class="btn btn-lg btn-wmffl btn-block" href="/admin" role="button">Commish</a>
                </div>
            <?php
            }
            ?>
                <div class="modal-footer">
                    <form class="form-signout" method="post" action="/login/logout">
                        <button type="submit" class="btn btn-lg btn-wmffl btn-block">Log Out</button>
                    </form>
                </div>
        </div>
    </div>
</div>

<main role="main" class="fluid-container px-4 pb-1 mb-3">
    <div class="starter-template">
