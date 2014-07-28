<?php

session_start();

class tumblr {

    protected $mysqli;
    public $client;
    public $requestHandler;
    public $sourceBlog;
    public $postId;
    public $posts = array();
    public $photos = array();
    public $video;

    public function init() {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET);
        $this->requestHandler = $this->client->getRequestHandler();
        $this->requestHandler->setBaseUrl('https://www.tumblr.com/');
    }

    public function __construct() {
        require_once '../vendor/autoload.php';
        $this->init();
    }

    protected function db_connect() {
        do {
            $this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

            if ($this->mysqli->connect_error) {
                die('Connect Error (' . $this->mysqli->connect_errno . ') '
                        . $this->mysqli->connect_error);

                /* In case database crashed */
                sleep(5);
            } else {
                //echo 'Database connected!!<br>';
            }
        } while ($this->mysqli->connect_error);
    }

    public function getRequestToken() {
        $response = $this->requestHandler->request('POST', 'oauth/request_token', array());
        $out = $result = $response->body;
        $data = array();
        parse_str($out, $data);

        $_SESSION['tumblr_oauth_token'] = $data['oauth_token'];
        $_SESSION['tumblr_oauth_token_secret'] = $data['oauth_token_secret'];

        header('Location: ' . 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $data['oauth_token']);
    }

    public function getAccessToken() {
        $this->client->setToken($_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);

        $response = $this->requestHandler->request('POST', 'oauth/access_token', array('oauth_verifier' => $_GET['oauth_verifier']));
        $out = $result = $response->body;
        $data = array();
        parse_str($out, $data);

        // and print out our new keys
        $_SESSION['tumblr_oauth_token'] = $data['oauth_token'];
        $_SESSION['tumblr_oauth_token_secret'] = $data['oauth_token_secret'];
    }

    public function checkBlog($exportBlog) {
        $info = $this->client->getUserInfo();
        foreach ($info->user->blogs as $blog) {
            if ($blog->name == $exportBlog) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getPostsFromBlog($exportBlog) {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);
        if ($this->checkBlog($exportBlog)) {
            $posts = $this->client->getBlogPosts($exportBlog);

            $response = json_encode($posts);
            $response = json_decode($response, TRUE);

            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            if ($response['blog']['posts'] != 0) {
                foreach ($response['posts'] as $post) {
                    $this->postId = $post['id'];
                    $this->blogName = $post['blog_name'];
                    switch ($post['type']) {
                        case 'text':
                            $postArr = array(
                                'slug' => $post['slug'],
                                'type' => $post['type'],
                                'format' => $post['format'],
                                'title' => $post['title'],
                                'body' => $post['body']
                            );
                            break;
                        case 'photo':
                            if (count($post['photos']) > 1) {
                                $this->downloadPhotos($post['photos']);
                                $postArr = array(
                                    'slug' => $post['slug'],
                                    'type' => $post['type'],
                                    'format' => $post['format'],
                                    'caption' => $post['caption'],
                                    'data' => $this->photos
                                );
                            } else {
                                $postArr = array(
                                    'slug' => $post['slug'],
                                    'type' => $post['type'],
                                    'format' => $post['format'],
                                    'caption' => $post['caption'],
                                    'source' => $post['photos'][0]['original_size']['url']
                                );
                            }
                            break;
                        case 'video':
                            $this->downloadVideo($post['video_url']);
                            $postArr = array(
                                'slug' => $post['slug'],
                                'type' => $post['type'],
                                'format' => $post['format'],
                                'caption' => $post['caption'],
                                'data' => $this->video
                            );
                            break;
                        default:
                            $postArr = array(
                                'slug' => 'test slug',
                                'type' => 'text',
                                'format' => 'html',
                                'title' => 'test post',
                                'body' => 'this is a test post'
                            );
                            break;
                    }
                    $postData = json_encode($postArr);
                    $this->insertPost($postData);
                }
            }
            header( "refresh:1;url=form_export.php" );
            echo "<h2>Success!</h2>";
        } else {
            header( "refresh:3;url=form_export.php" ); 
            echo "<h2>User has no such blog!!</h2>";
        }
    }

    public function getPosts() {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);
        $info = $this->client->getUserInfo();
        foreach ($info->user->blogs as $blog) {
            $posts = $this->client->getBlogPosts($blog->name);

            $response = json_encode($posts);
            $response = json_decode($response, TRUE);

            echo '<pre>';
            print_r($response);
            echo '</pre>';
            if ($response['blog']['posts'] != 0) {
                foreach ($response['posts'] as $post) {
                    $this->postId = $post['id'];
                    $this->blogName = $post['blog_name'];
                    switch ($post['type']) {
                        case 'text':
                            $postArr = array(
                                'slug' => $post['slug'],
                                'type' => $post['type'],
                                'format' => $post['format'],
                                'title' => $post['title'],
                                'body' => $post['body']
                            );
                            break;
                        case 'photo':
                            if (count($post['photos']) > 1) {
                                $this->downloadPhotos($post['photos']);
                                $postArr = array(
                                    'slug' => $post['slug'],
                                    'type' => $post['type'],
                                    'format' => $post['format'],
                                    'caption' => $post['caption'],
                                    'data' => $this->photos
                                );
                            } else {
                                $postArr = array(
                                    'slug' => $post['slug'],
                                    'type' => $post['type'],
                                    'format' => $post['format'],
                                    'caption' => $post['caption'],
                                    'source' => $post['photos'][0]['original_size']['url']
                                );
                            }
                            break;
                        case 'video':
                            $this->downloadVideo($post['video_url']);
                            $postArr = array(
                                'slug' => $post['slug'],
                                'type' => $post['type'],
                                'format' => $post['format'],
                                'caption' => $post['caption'],
                                'data' => $this->video
                            );
                            break;
                        default:
                            $postArr = array(
                                'slug' => 'test slug',
                                'type' => 'text',
                                'format' => 'html',
                                'title' => 'test post',
                                'body' => 'this is a test post'
                            );
                            break;
                    }
                    $postData = json_encode($postArr);
                    $this->insertPost($postData);
                }
            }
        }
    }

    public function downloadPhotos($photos) {
        foreach ($photos as $photo) {
            $pos = strrpos($photo['original_size']['url'], '/');
            $photoName = substr($photo['original_size']['url'], $pos + 1);
            $this->photos[] = "../photos/" . $photoName;
            $ch = curl_init($photo['original_size']['url']);
            $fp = fopen("../photos/" . $photoName, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
    }

    public function downloadVideo($videoUrl) {
        $pos = strrpos($videoUrl, '/');
        $videoName = substr($videoUrl, $pos + 1);
        $this->video = "../videos/" . $videoName;
        $ch = curl_init($videoUrl);
        $fp = fopen("../videos/" . $videoName, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    public function insertPost($post) {
        $stmt = $this->mysqli->stmt_init();
        $stmt = $this->mysqli->prepare('INSERT INTO posts (blog_name, post_id, post) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $this->blogName, $this->postId, $post);
        $stmt->execute();
        $stmt->close();
    }

    public function selectPosts($sourceBlog) {
        $stmt = $this->mysqli->stmt_init();
        if ($stmt->prepare("SELECT post FROM posts WHERE blog_name = ?")) {
            $stmt->bind_param("s", $sourceBlog);
            $stmt->execute();
            $stmt->bind_result($post);
            while ($stmt->fetch()) {
                $this->posts[] = $post;
            }
            $stmt->close();
        }
        if (empty($this->posts)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function testPosting() {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);
        $clientInfo = $this->client->getUserInfo();
        $blogs = !empty($clientInfo->user->blogs) ? $clientInfo->user->blogs : null;

        foreach ($blogs as $blog) {
            $this->client->createPost($blog->name, array(
                'slug' => 'test slug',
                'type' => 'text',
                'format' => 'html',
                'title' => 'test post',
                'body' => 'this is a test post'
            ));
        }
    }

    public function transferPost($sourceBlog) {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);
        $info = $this->client->getUserInfo();
        echo $info->user->blogs{0}->name;
        echo '<pre>';
        print_r($info->user->blogs);
        echo '</pre>';
        if ($this->selectPosts($sourceBlog)) {
            echo '<pre>';
            print_r($this->posts);
            echo '</pre>';
            foreach ($this->posts as $post) {
                $this->client->createPost($info->user->blogs{0}->name, json_decode($post, TRUE));
            }
        } else {
            header( "refresh:3;url=form_import.php" ); 
            echo "No such resource blog!";
        }
    }

    public function transferPost2($sourceBlog, $targetBlog) {
        if ($this->selectPosts($sourceBlog)) {
            // echo '<pre>';
            // print_r($this->posts);
            // echo '</pre>';
            foreach ($this->posts as $post) {
                $this->client->createPost($targetBlog, json_decode($post, TRUE));
            }
            header( "refresh:3;url=form_import.php" );
            echo "<h2>Success!</h2>";
        } else {
            header( "refresh:3;url=form_import.php" ); 
            echo "<h2>No such resource blog!</h2>";
        }
    }

    public function blogTransfer($sourceBlog, $targetBlog) {
        $this->client = new Tumblr\API\Client(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['tumblr_oauth_token'], $_SESSION['tumblr_oauth_token_secret']);
        if ($this->hasBlog($targetBlog)) {
            $this->transferPost2($sourceBlog, $targetBlog); 
        } else {
            header( "refresh:1;url=form_import.php" ); 
            echo "<h2>No target blog!! Please create first!!</h2>";
        }
    }

    public function hasBlog($blog) {
        $info = $this->client->getUserInfo();
        foreach ($info->user->blogs as $userBlog) {
            if ($userBlog->name == $blog) {
                return TRUE;
            }
        }
        return FALSE;
    }

}

