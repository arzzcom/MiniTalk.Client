<?php
/**
 * 이 파일은 미니톡 클라이언트의 일부입니다. (https://www.minitalk.io)
 *
 * 미니톡 클라이언트 관리자 로그아웃을 처리한다.
 * 
 * @file /process/login.php
 * @license MIT License
 * @modified 2025. 2. 7.
 */
if (defined('__MINITALK__') == false) exit;

unset($_SESSION['MINITALK_LOGGED']);
$results->success = true;
?>