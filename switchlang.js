<<<<<<< HEAD
const languageToggle = document.getElementById("language-toggle");
const loginTitle = document.getElementById("login-title");
const usernameLabel = document.getElementById("username-label");
const passwordLabel = document.getElementById("password-label");
const loginButton = document.getElementById("login-button");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");

function switchToThai() {
    loginTitle.textContent = "เข้าสู่ระบบ";
    usernameLabel.innerHTML = '<i class="fas fa-user"></i>';
    passwordLabel.innerHTML = '<i class="fas fa-lock"></i>';
    loginButton.textContent = "เข้าสู่ระบบ";
    usernameInput.placeholder = "กรุณากรอกชื่อผู้ใช้";
    passwordInput.placeholder = "กรุณากรอกรหัสผ่าน";
    languageToggle.textContent = "English";
}

function switchToEnglish() {
    loginTitle.textContent = "Login";
    usernameLabel.innerHTML = '<i class="fas fa-user"></i>';
    passwordLabel.innerHTML = '<i class="fas fa-lock"></i>';
    loginButton.textContent = "Log In";
    usernameInput.placeholder = "Enter your username";
    passwordInput.placeholder = "Enter your password";
    languageToggle.textContent = "ภาษาไทย";
}

languageToggle.addEventListener("click", () => {
    if (languageToggle.textContent === "English") {
        switchToEnglish();
    } else {
        switchToThai();
    }
});

switchToThai();
=======
const languageToggle = document.getElementById("language-toggle");
const loginTitle = document.getElementById("login-title");
const usernameLabel = document.getElementById("username-label");
const passwordLabel = document.getElementById("password-label");
const loginButton = document.getElementById("login-button");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");

function switchToThai() {
    loginTitle.textContent = "เข้าสู่ระบบ";
    usernameLabel.innerHTML = '<i class="fas fa-user"></i>';
    passwordLabel.innerHTML = '<i class="fas fa-lock"></i>';
    loginButton.textContent = "เข้าสู่ระบบ";
    usernameInput.placeholder = "กรุณากรอกชื่อผู้ใช้";
    passwordInput.placeholder = "กรุณากรอกรหัสผ่าน";
    languageToggle.textContent = "English";
}

function switchToEnglish() {
    loginTitle.textContent = "Login";
    usernameLabel.innerHTML = '<i class="fas fa-user"></i>';
    passwordLabel.innerHTML = '<i class="fas fa-lock"></i>';
    loginButton.textContent = "Log In";
    usernameInput.placeholder = "Enter your username";
    passwordInput.placeholder = "Enter your password";
    languageToggle.textContent = "ภาษาไทย";
}

languageToggle.addEventListener("click", () => {
    if (languageToggle.textContent === "English") {
        switchToEnglish();
    } else {
        switchToThai();
    }
});

switchToThai();
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
