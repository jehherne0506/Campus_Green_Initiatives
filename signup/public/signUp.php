<?php
    $errors = ["username" => "", "email" => "", "password" => "", "system" => ""];
    $success = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(empty($_POST["username"])){
            $errors["username"] = "Username is required";
        } elseif(strlen($_POST["username"]) < 2){
            $errors["username"] = "Username must be at least 2 characters";
        }

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


        if(!($errors["username"] || $errors["email"] || $errors["password"] || $errors["system"])){
            include "../conn.php";

            $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

            $sql = "INSERT INTO Users (username, email, password_hash) 
            VALUES ('$_POST[username]', '$_POST[email]', '$password_hash')";

            try{
                $result = mysqli_query($db_connect, $sql);
                $success = "Your Account is successfully registered. You may login with your account.";
            } catch(mysqli_sql_exception $e){
                if($e->getCode() == 1062){
                    $errors["email"] = "This email is already registered.";
                } else{
                    $errors["system"] = "An error has occured with our system. Please try again later.";
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
    <link href="signUp.css" rel="stylesheet">
</head>
<body>
    <video autoplay loop muted plays-inline class="back-video">
        <source src="./public/sign_up_background.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    <main class="sign_up">
        <div class="sign_up_form">
            <img id="logo" src="./public/logo-black.png" alt="EcoRiseAPU Logo"/>
            <h1 id="title">Welcome Aboard</h1>

            <?php if($success){ ?>
                <div class="register-success">
                    <?php echo $success; ?>
                </div>
            <?php }?>

            <?php if($errors["system"]){ ?>
                <div class="error-message system-error">
                    <?php echo $errors["system"]; ?>
                </div>
            <?php }?>

            <form method="post" action="">
                <div class="field">
                    <label for="username">
                        Username
                    </label>
                    <input id="username" name="username" type="text" class="input_field <?php echo $errors["username"] ? 'error_border' : '' ?>" placeholder="John Doe" />
                    <?php if($errors["username"]){ ?>
                        <span class="error-message">
                            <?php echo $errors["username"]; ?>
                        </span>
                    <?php } ?>
                </div>
                <div class="field">
                    <label for="email">
                        Email
                    </label>
                    <input id="email" name="email" type="text" class="input_field <?php echo $errors["email"] ? 'error_border' : '' ?>" placeholder="student@apu.edu.my" />
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
                        <input id="password" name="password" type="text" class="input_field <?php echo $errors["password"] ? 'error_border' : '' ?>" placeholder="••••••••" />
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
                    <button class="submit">Register</button>
                    <p id="switch_mode">Already have an account? <a href="../login/login.php">Login Now</a></p>
                </div>
            </form>
        </div>
        <div class="side-image">
            <h1><span id="element"></span></h1>
        </div>
    </main>

     <!-- Load library from the CDN -->
  <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

  <script>
    var typed = new Typed('#element', {
      strings: ['"The Earth is a fine place and worth <span id=\'highlight_word\'>fighting</span> for"'],
      typeSpeed: 50,
      backSpeed: 50,
      loop: true
    });
  </script>

  <script src="signUp.js"></script>
</body>
</html>