<?php
function normalize($val)
{
    if (!$val) return false;

    $val = preg_replace('~(\s)+(\-)+(\s)+~', "$1$3", $val); // убираем одинокие дефисы
    // оставляем буквы, цифры, пробелы и дефисы
    // переводим в верхний регистр
    $val = preg_replace('~[^\w\s\d\-]~', '', strtoupper($val));

    return $val;
}

function unique($words, $morph = FALSE)
{
    $total = sizeof($words);
    $counter = 0;
    $result = $marr = array();

    $unique = array_count_values($words);
    foreach ($words as $w)
    arsort($unique, SORT_NUMERIC);

    if ($morph)
    {
        $morphy = morphy_instance();
        //$morphy->getShmCache()->free();
        $unique_values = array_keys($unique);


        // пословная обработка
        foreach ($unique_values as $u)
        {

            $paradigms = $morphy->findWord($u);
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
                $marr[] = '{' . implode('|', $m) . '}';
            }
            else
            {
                $marr[] = '';
            }

            unset($paradigms);
        }

        /*
        // bulk-обработка
        $morph_words = $morphy->findWord($unique_values);

        foreach ($morph_words as $key => $word) {
            if ($word) {
                $m = array();
                foreach ($word as $paradigms) {
                    foreach ($paradigms->getFoundWordForm() as $paradigm) {
                        $m[] = $paradigm->getWord() .'='. $paradigm->getPartOfSpeech() . ',' . implode(',', $paradigm->getGrammems());
                    }
                }
            }
            $marr[] = $word ? '{' . implode('|', $m) . '}' : '';
        }
        */

    }


    foreach ($unique as $word => $count)
    {
        $result[$word] = array(
            $count,
            round($count / $total * 100, 4) . '%',
         );
        if ($morph) $result[$word][] = $marr[$counter];
        $counter++;
    }

    return $result;
}

function remove_stop($words, $stop_words)
{
    if (!$words OR !$stop_words) return false;
    return  array_diff($words, $stop_words);
}

function create_dic($data, $filename, $dir = 'tmp')
{
    $filename = $filename .'_'.substr(sha1(microtime()), 0, 10).'.txt';
    $path = DICROOT . $dir;
    if (is_dir($path) AND is_writable($path)) {
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
    else die('Не удаётся открыть директорию tmp на запись');
}

function morphy_instance()
{
    // Создание экземпляра морфи
    $dic_path = DICROOT . 'phpmorphy/dicts';
    $lang = 'ru_RU';
    define('PHPMORPHY_SHM_SEGMENT_SIZE', 256 * 1024 * 1024);
    $morphy_opts = array(
        'storage' => PHPMORPHY_STORAGE_MEM,
    );

    try {
        return new phpMorphy($dic_path, $lang, $morphy_opts);
    } catch (phpMorphy_Exception $e) {
        die('Произошла ошибка при создании морфологического анализатора ' . $e->getMessage());
    }
}