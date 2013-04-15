<?php
/*
 * Анализатор русских текстов
 * @author Vlad B. @ hi@vladbarinov.ru
 */

define('DICROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once 'phpmorphy/src/common.php';
require_once 'funcs.php';
require_once 'Benchmark.php';

ini_set('display_errors', 'On');
ini_set('post_max_size', '4M');
error_reporting(E_ALL | E_STRICT);
if (!setlocale(LC_ALL, 'ru_RU.CP1251')) die('В системе нет локали ru_RU.CP1251');
$fmsize = str_replace(array('M','K','b'), '', ini_get('post_max_size'));

$app = Benchmark::start('Приложение');

$stats = array();
$text = '';
$errors = array();
$words = array();
$unique = array();
$files = array();
$stop_words = explode(',', @file_get_contents(DICROOT . 'stop_words_ru.txt'));

// Юзер загрузил файл
if (isset($_FILES['file']) AND is_uploaded_file($_FILES['file']['tmp_name']))
{
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $st = Benchmark::start('Нормализация и разбиение текста');
        $text = file_get_contents($_FILES['file']['tmp_name']);

        $norm_text = normalize(trim($text));

        $split_pattern = '/(?<!-)\b[\s]*(?!-)/';
        $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);

        Benchmark::stop($st);
    }
    else
    {
        echo upload_error($_FILES['file']['error']);
    }

}
else if (isset($_POST) AND !empty($_POST['text'])) // Ввод текста через форму
{
    $st = Benchmark::start('Нормализация и разбиение текста');

    $text = substr(trim($_POST['text']), 0, 200000);

    $norm_text = normalize($text);

    $split_pattern = '/(?<!-)\b[\s]*(?!-)/';
    $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);

    Benchmark::stop($st);
}

if (sizeof($words))
{
    $freq = Benchmark::start('Составление словаря');
    list($dict, $dict_dynamics, $zipf) = unique($words);
    Benchmark::stop($freq);

    $stop = Benchmark::start('Выделение стоп-слов');
    $sane_words = remove_stop($words, $stop_words);
    list($dict_stop) = unique($sane_words);
    Benchmark::stop($stop);
}

// Создание нужных файлов словарей
if (isset($_POST['freq']) AND !empty($dict))
{
    // Обычный частотный словарь
    $files['Частотный словарь'] = create_dic($dict, 'freq');
}

if (isset($_POST['stop']) AND !empty($dict_stop))
{
    // словарь после удаления стоп слов
    $files['Частотный словарь (без стоп-слов)'] = create_dic($dict_stop, 'stop');

}

if (isset($_POST['morph']) AND !empty($sane_words))
{
    $morph = Benchmark::start('Морфолгия');

    if (isset($_POST['mtype']) AND $_POST['mtype'] == 1)
    { // mystem
        if ($dict_morph = mystem($sane_words)) {
            $files['Частотный словарь (без стоп-слов, c морфологией)'] = $dict_morph;
        }
    }
    else if (isset($_POST['mtype']) AND $_POST['mtype'] == 2)
    { // phpmorphy
        list($dict_morph) = unique($sane_words, TRUE);
        $files['Частотный словарь (без стоп-слов, c морфологией)'] = create_dic($dict_morph, 'morph');
    }

    Benchmark::stop($morph);
}

// Для статистики
if ($text)
{
    $number_of_symbols = strlen($text);
    $spaces = substr_count($text, ' ');

    $stop_words_actual = array_intersect($words, $stop_words);
    list($stop_words_array) = unique($stop_words_actual);
}

Benchmark::stop($app);
$stats = Benchmark::statistics();

// Основной шаблон
include('view.php');