<html>
<head>
    <title>Zenbership Membership Software | Setup</title>
    <link href="assets/setup.css" rel="stylesheet" type="text/css"/>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script type="text/javascript">

        $(document).ready(function() {
            $("#connectionButton").click(function() {
                $('#connection').html('Testing...');

                var data = {
                    host: $('#mysqlhost').val(),
                    db: $('#mysqldb').val(),
                    user: $('#mysqluser').val(),
                    pass: $('#mysqlpass').val()
                };

                console.log(data);

                $.getJSON("functions/testMysql.php", data, function(data) {
                    console.log(data);

                    if (data.error) {
                        $('#connection').html('Failed: ' + data.msg);
                    } else {
                        $('#connection').html('Success!');
                    }

                });
            });
        });

    </script>
</head>
<body>

<div class="topbar">
    <img src="../admin/imgs/logo.png" />
</div>

<form action="process.php" method="post">
    <div class="holder">