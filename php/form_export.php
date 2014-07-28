
<!DOCTYPE html>
<html>
    <head>
        <title>Tumblr Transfer Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="assets/template/css/bootstrap.min.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Alef:400,700' rel='stylesheet' type='text/css'>
        <link href="assets/template/snippet.css" rel="stylesheet">
    </head>
    <body>
        <section>
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="offset1 span8 pull-center bootshape" >
                        <form class="simple-login" role="form" action="export.php" method="GET">
                            <?php
                                // echo isset($_SESSION['msg']) ? ('<div class="alert alert-success">' . $_SESSION['msg'] . '</div>') : '';
                                // echo isset($_SESSION['error']) ? ('<div class="alert alert-danger">' . $_SESSION['error'] . '</div>') : '';
                            ?>
                            <h1>Export Blog</h1>&nbsp;&nbsp;&nbsp;
                            <div class="row form-row">
                                <div class="col-sm-12">
                                    <label name= "current" type="username" class="form-control">Exporting Blog Name:</label>
                                </div> 
                            </div>
                            <div class="row form-row">
                                <div class="col-sm-12">
                                    <input name="blog" type="username" required placeholder="Blog name" class="form-control">
                                </div>
                            </div>
                            <button name="export" type="submit" class="btn btn-lg btn-primary btn-block">Export</button>
                        </form>
                        <p><a href="form_import.php">Importing blog</a></p>&nbsp;&nbsp;&nbsp;
                    </div>


                </div>
            </div>
        </section>
        <script src="https://code.jquery.com/jquery.js"></script>
        <script src="assets/template/js/bootstrap.min.js"></script>
        <script src="assets/template/snippet.js"></script>
    </body>
</html>




