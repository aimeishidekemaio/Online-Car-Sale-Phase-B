<?php
// Logout.php
session_start();
session_unset();  // 清空session变量
session_destroy();  // 销毁session
header("Location: Login.php");  // 跳转登录页
exit();
?>