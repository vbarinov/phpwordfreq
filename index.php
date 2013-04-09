<?php
require_once 'phpmorphy/src/common.php';
require_once 'funcs.php';
require_once 'UTF8.php';
require_once 'Benchmark.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
define('DICROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

$app = Benchmark::start('Приложение');

$stats = array();
$text = '';
$errors = array();
$words = array();
$unique = array();
$files = array();
$stop_words = explode(',', @file_get_contents(DICROOT . 'stop_words_ru.txt'));

// Юзер загрузил файл
if (isset($_FILES['file']) AND strlen($_FILES['file']['tmp_name']))
{
    $text = file_get_contents($_FILES['file']['tmp_name']);

    $st = Benchmark::start('Нормализация и разбиение текста');

    $norm_text = normalize($text, array('Cyrillic'));

    //$split_pattern = '/\b[\(\)\.\-\',:!\?;"\{\}\[\]„“»«‘`\t\r\n\d\s]*/u';
    $split_pattern = '/(?<!-)\b[\s]*(?!-)/u';
    $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);


    //$dict = unique($words);

    Benchmark::stop($st);
}
 else if (isset($_POST) AND !empty($_POST['text'])) // Текст через инпут
{
    // разбиение текста на слова
    $st = Benchmark::start('Нормализация и разбиение текста');

    $text = substr($_POST['text'], 0, 200000);

    $norm_text = normalize($text, array('Cyrillic'));

    $split_pattern = '/(?<!-)\b[\s]*(?!-)/u';
    $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);

    //$dict = unique($words);

    Benchmark::stop($st);

}

if (sizeof($words)) {
    $freq = Benchmark::start('Составление словаря');
    $dict = unique($words);
    Benchmark::stop($freq);

    $stop = Benchmark::start('Выделение стоп-слов');
    $sane_words = remove_stop($words, $stop_words);
    $dict_stop = unique($sane_words);
    Benchmark::stop($stop);
}

// Создание нужных файлов
if (isset($_POST['freq']) AND !empty($dict)) {
    // Обычный частотный словарь
    $files['Частотный словарь'] = create_dic($dict, 'frequence');

}
if (isset($_POST['stop']) AND !empty($dict_stop))
{
    // словарь после удаления стоп слов
    //die(print_r(array_flip($stop_words)));
    $files['Частотный словарь (без стоп-слов)'] = create_dic($dict_stop, 'stop');

}
if (isset($_POST['morph']) AND !empty($sane_words))
{
    $morph = Benchmark::start('Морфолгия');

    //$sane_words = remove_stop($words, $stop_words);
    $dict_morph = unique($sane_words, TRUE);

    $files['Частотный словарь (без стоп-слов, c морфологией)'] = create_dic($dict_morph, 'morph');
    Benchmark::stop($morph);

}

// stats
if ($text) {
    $number_of_symbols = UTF8::strlen($text);
    $spaces = mb_substr_count($text, ' ', 'utf-8');

    $stop_words_actual = array_intersect($words, $stop_words);
    $stop_words_array = unique($stop_words_actual);
}

Benchmark::stop($app);
$stats = Benchmark::statistics();

include('view.php');