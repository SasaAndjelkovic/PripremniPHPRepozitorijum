<?php

    function clean($string) {
        return htmlentities($string);
    }

    function redirect($location) {
        header(header: "location: {$location}");
        exit();
    }

    function set_message($message) {
        if(!empty($message)) {
            $_SESSION['message'] = $message;
        } else {
            $message = "";
        }
    }

    function display_message() {
        if(isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
    }

    function email_exists($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $query = "SELECT id FROM users WHERE email = '$email'";
        $result = query($query);

        if($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function user_exists($user) {
        $user = filter_var($user, FILTER_SANITIZE_STRING);
        $query = "SELECT id FROM users WHERE username  = '$user'";
        $result = query($query);

        if($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function validate_user_registration() {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $first_name = clean($_POST['first_name']);
            $last_name = clean($_POST['last_name']);
            $username = clean($_POST['username']);
            $email = clean($_POST['username']);
            $password = clean($_POST['password']);
            $confirm_password = clean($_POST['confirm_password']);

            if(strlen($first_name) < 3) {
                $errors[] = "Your First Name cannot be less then 3 characters!";
            }
            if(strlen($last_name) < 3) {
                $errors[] = "Your Last Name cannot be less then 3 characters!";
            }
            if(strlen($username) < 3) {
                $errors[] = "Your Userame cannot be less then 3 characters!";
            }
            if(strlen($username) > 20) {
                $errors[] = "Your Userame cannot be bigger then 20 characters!";
            }
            if(email_exists($email)) {
                $errors[] = "Sorry that Email is alredy taken!";
            }
            if(user_exists($username)) {
                $errors[] = "Sorry that Username is alredy is taken!";
            }
            if(strlen($password) < 8) {
                $errors[] = "Your Password cannot be less then 8 characters";
            }
            if($password != $confirm_password) {
                $errors[] = "The password was not confirmed correctly";
            }

            if(!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert'>" . $error . "</div>";
                }
            } else {
                $first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
                $last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
                $username = filter_var($username, FILTER_SANITIZE_STRING);
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                $password = filter_var($password, FILTER_SANITIZE_STRING);
                create_user($first_name, $last_name, $username, $email, $password);
            }
        }
    }

    function create_user($first_name, $last_name, $username, $email, $password) {
        
            $first_name = escape($first_name);
            $last_name = escape($last_name);
            $username = escape($username);
            $email = escape($email);
            $password = escape($password);
            $password = password_hash($password, algo: PASSWORD_DEFAULT);
    
            $sql = "INSERT INTO users(first_name, last_name, username, profile_image, email, password) ";
            $sql .= "VALUES('$first_name', '$last_name', '$username', 'uploads/default.jpg', '$email', '$password')";
    
            confim(query($sql));
            set_message("You have been succesfully registred! Please Log in!");
            redirect(location: "login.php");
    }

    function validate_user_login() {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $email = clean($_POST['email']);
            $password = clean($_POST['password']);

            if(empty($email)) {
                $errors[] = "Email field cannot be empty";
            }
            if(empty($password)) {
                $errors[] = "Password field cannot be empty";
            }

            if(empty($errors)) {
                if(user_login($email, $password)) {
                    redirect(location: "index.php");
                } else {
                    $errros[] = "Your email or password is incorrect, please try again";
                }
            } else {
                foreach ($errors as $error) {
                    echo '<div class="alert">' . $error . '</div>';
                }
            }
        }
    }

    function user_login($email, $password) {
        $password = filter_var($password, FILTER_SANITIZE_STRING);
        $email = filter_var($email, FILTER_SANITIZE_STRING);

        $query = "SELECT * FROM users WHERE email='$email'";
        $result = query($query);

        if($result->num_rows > 0) {
            $data = $result->fetch_assoc();

            if(password_verify($password, $data['password'])) {
                $_SESSION['email'] = $email;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }