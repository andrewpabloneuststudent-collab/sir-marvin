<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales POS Verification</title>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
}

button {
    padding: 10px 15px;
    margin: 5px;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    color: #fff;
}

.btn-senior {
    background: #007bff;
}

.btn-pwd {
    background: #28a745;
}
</style>
</head>

<body>

<h2>Sales POS - ID Verification</h2>

<!-- BUTTONS -->
<button class="btn-senior" onclick="openSenior()">Verify Senior ID</button>
<button class="btn-pwd" onclick="openPWD()">Verify PWD ID</button>

<script>
// ✅ SAME STYLE AS PWD (POPUP WINDOW)
function openSenior() {
    window.open(
        "https://www.ncsc.gov.ph/registration-verification",
        "Senior Verification",
        "width=900,height=600"
    );
}

function openPWD() {
    window.open(
        "https://pwd.doh.gov.ph/tbl_pwd_id_verificationlist.php",
        "PWD Verification",
        "width=900,height=600"
    );
}
</script>

</body>
</html>