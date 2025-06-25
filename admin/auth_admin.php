<?php
session_start();


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>