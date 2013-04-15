<?php defined('DICROOT') or die('Доступ запрещён');
/*
 * Функции
 */

function normalize($val)
{
    if (!$val) return false;

    $val = preg_replace('/([\s\d]+\-(\s)*)/', " ", $val); // убираем одинокие дефисы
    // оставляем буквы, пробелы и дефисы
    // переводим в верхний регистр
    $val = preg_replace('/[^а-я\s\-]/i', '', strtoupper($val));

    return $val;
}

function unique($words, $morph = FALSE)
{
    $total = sizeof($words); // количество слов
    $counter = 0;
    $result = $unique = $dict_dynamics = $zipf = array();
    if ($morph) $morphy = morphy_instance();

    //$counted_words = arsort(array_count_values($words));

    // формирование словаря
    foreach ($words as $n => $w)
    {
        if (!array_key_exists($w, $unique))
        {
            // добавляем слово в словарь
            $unique[$w] = 1;
            $counter++;
        }
        else
        {
            // или увеличиваем число вхождений
            $unique[$w]++;
        }

        $dict_dynamics[$n + 1] = $counter; // размер словаря
    }
    arsort($unique, SORT_NUMERIC);

    foreach ($unique as $word => $count)
    {
        $freq = $count / $total * 100;
        $result[$word] = array(
            $count,
            round($freq, 4) . '%',
        );
        if ($morph) $result[$word][] = analyze($word, $morphy);
        $zipf[] = $freq;
    }

    return array($result, $dict_dynamics, $zipf);
}

function analyze($word, &$morphy)
{
    $result = '';

    $paradigms = $morphy->findWord($word);
    if ($paradigms !== FALSE)
    {
        $m = array();
        foreach ($paradigms as $paradigm)
        {
            foreach ($paradigm->getFoundWordForm() as $form)
            {
                $m[] = $form->getWord() .'='. $form->getPartOfSpeech() . ',' . implode(',', $form->getGrammems());
            }

        }
        $result = '{' . implode('|', $m) . '}';
    }

    //unset($paradigms);
    return $result;
}

function remove_stop($words, $stop_words)
{
    if (!$words OR !$stop_words) return false;
    return  array_diff($words, $stop_words);
}

function mystem($data, $dir = 'tmp')
{
    if (!$data) return false;

    // Запись временного файла (без стоп слов)
    $filename = substr(sha1(microtime()), 0, 20).'.txt';
    $path = DICROOT . $dir;
    if (is_dir($path) AND is_writable($path))
    {
        $path .= DIRECTORY_SEPARATOR . $filename;
        if (!$handle = fopen($path, 'c+b')) die('Невозможно создать файл '.$filename);
        if (is_array($data))
        {
            foreach ($data as $k => $v)
            {
                $line = $v . PHP_EOL;
                fwrite($handle, $line);
            }
        }
        else
        {
            fwrite($handle, $data);
        }

        fclose($handle);
    }
    else
        die('Не удаётся открыть директорию '. $dir .' на запись');

    // Анализ
    if (file_exists($path) AND is_readable($path))
    {
        $output_file = 'morph_'.substr(sha1(microtime()), 0, 10).'.txt';
        $output_path = DICROOT . $dir . DIRECTORY_SEPARATOR . $output_file;
        if (!$handle = fopen($output_path, 'c+b')) die('Невозможно создать файл '.$output_file);
        exec(DICROOT.'mystem -nfi '.$path.' '.$output_path);
        fclose($handle);
    }

    if (file_exists($output_path))
    {
        return array(
            'file' => '/' . $dir . '/' . $output_file,
            'size' => round(filesize($output_path) / 1024, 2)
        );
    }

    return false;

}

function create_dic($data, $filename, $dir = 'tmp')
{
    $filename = $filename .'_'.substr(sha1(microtime()), 0, 10).'.txt';
    $path = DICROOT . $dir;
    if (is_dir($path) AND is_writable($path))
    {
        $path .= DIRECTORY_SEPARATOR . $filename;
        if (!$handle = fopen($path, 'c+b')) die('Невозможно создать файл '.$filename);
        foreach ($data as $k => $v)
        {
            $line = $k . ':' . (is_array($v) ? implode(':', $v) : $v) . PHP_EOL;
            fwrite($handle, $line);
        }

        fclose($handle);

        return array(
            'file' => '/' . $dir . '/' . $filename,
            'size' => round(filesize($path) / 1024, 2)
        );
    }
    else
        die('Не удаётся открыть директорию '. $dir .' на запись');
}

function morphy_instance()
{
    // Создание экземпляра морфи
    $dic_path = DICROOT . 'phpmorphy/dicts';
    $lang = 'ru_RU';
    //define('PHPMORPHY_SHM_SEGMENT_SIZE', 64 * 1024 * 1024);
    $morphy_opts = array(
        'storage' => PHPMORPHY_STORAGE_SHM,
    );

    try {
        return new phpMorphy($dic_path, $lang, $morphy_opts);
    } catch (phpMorphy_Exception $e) {
        die('Произошла ошибка при создании морфологического анализатора ' . $e->getMessage());
    }
}

function upload_error($code)
{
    switch($code)
    {
        case UPLOAD_ERR_INI_SIZE:
            $message = "Превышен максимальный размер файла";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $message = "Превышен указанный в форме максимальный размер файла";
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = "Файл загружен не полностью";
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = "Файл не был загружен";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = "Не найдена временная директория";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = "Не удалось записать файл на диск";
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = "Загрузка файла остановлена расширением";
            break;
        default:
            $message = "Неизвестная ошибка загрузки";
            break;
    }
    return $message;
}