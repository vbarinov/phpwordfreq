<?php
require_once 'phpmorphy/src/common.php';
require_once 'funcs.php';
//require_once 'UTF8.php';
require_once 'Benchmark.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
define('DICROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
setlocale(LC_ALL, 'ru_RU.CP1251');
$fmsize = ini_get('post_max_size');

$app = Benchmark::start('����������');

$stats = array();
$text = '';
$errors = array();
$words = array();
$unique = array();
$files = array();
$stop_words = explode(',', @file_get_contents(DICROOT . 'stop_words_ru.txt'));

// ���� �������� ����
if (isset($_FILES['file']) AND strlen($_FILES['file']['tmp_name']))
{
    $text = file_get_contents($_FILES['file']['tmp_name']);

    $st = Benchmark::start('������������ � ��������� ������');

    $norm_text = normalize($text);

    //$split_pattern = '/\b[\(\)\.\-\',:!\?;"\{\}\[\]�����`\t\r\n\d\s]*/u';
    $split_pattern = '/(?<!-)\b[\s]*(?!-)/';
    $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);
    die(print_r($words));

    Benchmark::stop($st);
}
 else if (isset($_POST) AND !empty($_POST['text'])) // ����� ����� �����
{
    // ��������� ������ �� �����
    $st = Benchmark::start('������������ � ��������� ������');

    $text = substr($_POST['text'], 0, 200000);

    $norm_text = normalize($text);

    $split_pattern = '/(?<!-)\b[\s]*(?!-)/';
    $words = preg_split($split_pattern, $norm_text, NULL, PREG_SPLIT_NO_EMPTY);

    Benchmark::stop($st);

}

if (sizeof($words)) {
    $freq = Benchmark::start('����������� �������');
    $dict = unique($words);
    Benchmark::stop($freq);

    $stop = Benchmark::start('��������� ����-����');
    $sane_words = remove_stop($words, $stop_words);
    $dict_stop = unique($sane_words);
    Benchmark::stop($stop);
}

// �������� ������ ������
if (isset($_POST['freq']) AND !empty($dict)) {
    // ������� ��������� �������
    $files['��������� �������'] = create_dic($dict, 'frequence');

}
if (isset($_POST['stop']) AND !empty($dict_stop))
{
    // ������� ����� �������� ���� ����
    //die(print_r(array_flip($stop_words)));
    $files['��������� ������� (��� ����-����)'] = create_dic($dict_stop, 'stop');

}
if (isset($_POST['morph']) AND !empty($sane_words))
{
    $morph = Benchmark::start('���������');

    //$sane_words = remove_stop($words, $stop_words);
    $dict_morph = unique($sane_words, TRUE);

    $files['��������� ������� (��� ����-����, c �����������)'] = create_dic($dict_morph, 'morph');
    Benchmark::stop($morph);

}

// stats
if ($text) {
    $number_of_symbols = strlen($text);
    $spaces = substr_count($text, ' ');

    $stop_words_actual = array_intersect($words, $stop_words);
    $stop_words_array = unique($stop_words_actual);
}

Benchmark::stop($app);
$stats = Benchmark::statistics();

include('view.php');