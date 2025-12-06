<?php
session_start();
// Default language
$default_lang = 'en';
// Available languages
$available_languages = array(
    'en' => 'English',
    'mn' => 'Монгол',
    'fr' => 'Français'
);
// Get selected language from session or URL parameter
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $available_languages)) {
    $_SESSION['language'] = $_GET['lang'];
} elseif (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $default_lang;
}
$current_language = $_SESSION['language'];
// Load language file
$lang_file = __DIR__ . '/../languages/' . $current_language . '.php';
if (file_exists($lang_file)) {
    include $lang_file;
} else {
    include __DIR__ . '/../languages/' . $default_lang . '.php';
}
?>