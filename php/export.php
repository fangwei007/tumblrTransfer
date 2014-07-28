<?php
require_once 'config_export.php';
require_once 'tumblr.php';

class export extends tumblr {

    public function __construct() {
        parent::__construct();
        if (isset($_GET['redirect'])) {
            $this->db_connect();
            // $this->getPosts();
            if(isset($_SESSION['export_blog'])) {
                $this->getPostsFromBlog($_SESSION['export_blog']);
                // header('Location: form_export.php');
            } else {
                echo "exporting blog info needed!!";
            }
            $this->mysqli->close();
        } else {
            //?blog=thewaynetarget
            if(isset($_GET['blog'])) {
                $_SESSION['export_blog'] = $_GET['blog'];
            }
            if (isset($_SESSION['tumblr_oauth_token']) && isset($_SESSION['tumblr_oauth_token_secret']) && isset($_GET['oauth_verifier'])) {
                $this->getAccessToken();
            } else {
                $this->getRequestToken();
            }
        }
    }

    public function getAccessToken() {
        parent::getAccessToken();
        header('Location: export.php?redirect=1');
    }

}

$export = new export();
