<?php

require_once 'config_import.php';
require_once 'tumblr.php';

class import extends tumblr {

    public function __construct() {
        parent::__construct();
        if (isset($_GET['redirect'])) {
            $this->db_connect();
            if (isset($_SESSION['source_blog']) && isset($_SESSION['target_blog'])) {
                $this->blogTransfer($_SESSION['source_blog'], $_SESSION['target_blog']);
            } else {
                echo "Source blog and target blog info needed!!";
            }
            $this->mysqli->close();
        } else {
            //?source=thewaynetarget&target=thewaynedaily
            if (isset($_GET['source']) && isset($_GET['target'])) {
                $_SESSION['source_blog'] = $_GET['source'];
                $_SESSION['target_blog'] = $_GET['target'];
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
        header('Location: import.php?redirect=1');
    }

}

$import = new import();
