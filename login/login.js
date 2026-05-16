function togglePasswordVisibility(){
    const password_box = document.querySelector(".password_box");
    const password_input = document.querySelector("#password");

    // add visible class to password_box element if it doesn't have one, remove visible class if it have one
    password_box.classList.toggle("visible");

    // determine if password_box element contains visible class
    if(password_box.classList.contains("visible")){
        password_input.type = "text";
    } else{
        password_input.type = "password";
    }
}