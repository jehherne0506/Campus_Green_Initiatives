<?php
    session_start();
    $success = "";
    $errors = ["email" => "", "password" => "", "system" => ""];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(empty($_POST["email"])){
            $errors["email"] = "Email is required";
        } elseif(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
            $errors["email"] = "Invalid Email Format";
        }

        if(empty($_POST["password"])){
            $errors["password"] = "Password is required";
        } elseif(strlen($_POST["password"]) < 8){
            $errors["password"] = "Password must be at least 8 characters";
        }

        if(!($errors["email"] || $errors["password"])){
            include "../conn.php";
            $sql = "SELECT * FROM Users WHERE email = '{$_POST['email']}'";

            $result = mysqli_query($db_connect, $sql);

            if(!$result){
                $errors["system"] = "An error has occured with our system. Please try again later.";
            } else{
                // fetches a single row from the result set as an associative array
                $user = mysqli_fetch_assoc($result);
                if($user){
                    if(password_verify($_POST['password'], $user['password_hash'])){
                        if($user['account_status'] === "BANNED"){
                            $errors["system"] = "Your account is banned. Please contact the admin, admin@apu.edu.my for further enquiry.";
                        } else{
                            $_SESSION['user_id'] = $user['user_id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['avatar'] = $user['avatar'];

                            $cookie_name = "user_id";
                            $cookie_value = $user['user_id'];
                            setcookie($cookie_name, $cookie_value, time() + 7*24*60*60, "/");
                            
                            switch($user['role']){
                                case "STUDENT":
                                    header("Location: ../student/dashboard.php");
                                    exit();
                                case "ORGANIZER":
                                    header("Location: ../organizer/dashboard.php");
                                    exit();
                                case "ADMIN":
                                    header("Location: ../admin/dashboard.php");
                                    exit();
                            }
                        }
                    } else{
                        $errors["system"] = "Your credentials is incorrect. Please login again.";
                    }
                } else{
                    $errors["system"] = "Your credentials is incorrect. Please login again.";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRiseAPU</title>
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <link href="login.css" rel="stylesheet">
</head>
<body>
    <video autoplay loop muted plays-inline class="back-video">
        <source src="./public/login_background.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    <main class="login">
        <div class="side-image">
            <h1><span id="element"></span></h1>
        </div>
        <div class="login_form">
            <img id="logo" src="./public/logo-black.png" alt="EcoRiseAPU Logo"/>
            <h1 id="title">Welcome Back</h1>

            <?php if($errors["system"]){ ?>
                <div class="error-message system-error">
                    <?php echo $errors["system"]; ?>
                </div>
            <?php }?>

            <form method="post" action="">
                <div class="field">
                    <label for="email">
                        Email
                    </label>
                    <input id="email" type="text" name="email" class="input_field <?php echo $errors["email"] ? 'error_border' : '' ?>" placeholder="student@apu.edu.my" />
                    <?php if($errors["email"]){ ?>
                        <span class="error-message">
                            <?php echo $errors["email"]; ?>
                        </span>
                    <?php } ?>
                </div>
                <div class="field">
                    <label for="password">
                        Password
                    </label>
                    <div class="password_box">
                        <input id="password" type="text" name="password" class="input_field <?php echo $errors["password"] ? 'error_border' : '' ?>" placeholder="••••••••" />
                        <div class="password_icon">
                            <img src="./public/eye_close.png" class="eye_close" onclick="togglePasswordVisibility()" />
                            <img src="./public/eye_open.png" class="eye_open" onclick="togglePasswordVisibility()" />
                        </div>
                    </div>
                    <?php if($errors["password"]){ ?>
                        <span class="error-message">
                            <?php echo $errors["password"]; ?>
                        </span>
                    <?php } ?>
                </div>
                <div class="field">
                    <button class="sign-in">Sign In</button>
                    <p id="switch_mode">Doesn't have an account? <a href="../signup/signUp.php">Sign Up Now</a></p>
                </div>
            </form>
        </div>
    </main>

     <!-- Load library from the CDN -->
  <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

  <!-- Setup and start animation! -->
  <script>
    var typed = new Typed('#element', {
      strings: ['"Recycle today for a better <span id=\'highlight_word\'>Tomorrow</span>"'],
      typeSpeed: 50,
      backSpeed: 50,
      loop: true
    });
  </script>

  <script src="login.js"></script>
</body>
</html>