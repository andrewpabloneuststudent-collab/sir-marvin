document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("barcodeInput");
    const form = document.getElementById("posForm");

    // IMPORTANT: only run if elements exist on this page
    if (!input || !form) return;

    input.addEventListener("keydown", function (e) {

        if (e.key === "Enter") {
            e.preventDefault();

            // NORMAL submit (do NOT use prototype)
            form.submit();
        }

    });

    // always ready for scanning
    input.value = "";
    input.focus();

});

// VOID BUTTON HANDLER
document.querySelectorAll(".voidBtn").forEach(btn => {
    btn.addEventListener("click", function () {

        let index = this.getAttribute("data-index");

        let password = prompt("Enter VOID password:");

        if (!password) return;

        document.getElementById("void_index").value = index;
        document.getElementById("void_password").value = password;
        document.getElementById("action").value = "void_item";

        document.getElementById("posForm").submit();
    });
});