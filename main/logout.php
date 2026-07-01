<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Logout</title>
</head>
<body>
    <script>
        // ออกจากระบบ: ลบ access token ที่เก็บไว้ใน localStorage แล้วกลับไปหน้า login
        localStorage.removeItem("bo_access_token");
        window.location.replace("../index");
    </script>
</body>
</html>
