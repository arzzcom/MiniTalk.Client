<?php
/**
 * 이 파일은 미니톡 클라이언트의 일부입니다. (https://www.minitalk.io)
 *
 * 플러그인 폴더 접근을 막기 위한 인덱스파일
 * 
 * @file /plugins/index.php
 * @license MIT License
 * @modified 2025. 2. 7.
 */
header("HTTP/1.1 403 Forbidden");
header('location:../');
?>