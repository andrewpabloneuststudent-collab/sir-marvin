const toggle = document.getElementById("togglePassword");
const password = document.getElementById("password");

toggle.addEventListener("click", function () {
    const type = password.type === "password" ? "text" : "password";
    password.type = type;

    // Change icon
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});
