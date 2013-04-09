<?php
function normalize($val, $langs = FALSE)
{
    if (!$val) return false;
    if ($langs) {
        foreach ($langs as $lang)
        {
            $l[] = '\p{'.$lang.'}++';
        }
        $match_symbols = implode(' | ', $l);
    } else {
        $match_symbols = '\p{L}++';
    }

    $val = preg_replace('~(\s)+(\-)+(\s)+~u', "$1$3", $val); // убираем одинокие дефисы
    // оставляем буквы нужного языка, цифры, пробелы и дефисы
    // переводим в верхний регистр
    $val = preg_replace('~[^'.$match_symbols.'\s\d\-]~u', '', UTF8::strtoupper($val));

    return $val;
}

function unique($words, $morph = FALSE)
{
    $total = sizeof($words);
    $counter = 0;
    $result = $marr = array();

    $unique = array_count_values($words);
    arsort($unique, SORT_NUMERIC);

    if ($morph)
    {
        $morphy = morphy_instance();
        //$morphy->getShmCache()->free();
        $unique_values = array_keys($unique);

        /*
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
        */

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

    }


    foreach ($unique as $word => $count) {
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
        'storage' => PHPMORPHY_STORAGE_SHM,
    );

    try {
        return new phpMorphy($dic_path, $lang, $morphy_opts);
    } catch (phpMorphy_Exception $e) {
        die('Произошла ошибка при создании морфологического анализатора ' . $e->getMessage());
    }
}

function parse_words($text)
{
    preg_match_all('~(?>#1 letters
							(	#\p{L}++
								(?>\p{Cyrillic}++)
								#special
								(?>		\#     (?!\p{L}|\d)		#programming languages: C#
									|	\+\+?+ (?!\p{L}|\d)		#programming languages: C++, T++, B+ trees, Европа+; but not C+E, C+5
								)?+
							)

							#2 numbers
							|	(	\d++            #digits
									(?> % (?!\p{L}|\d) )?+	#brand names: 120%
								)
							#|	\p{Nd}++  #decimal number
							#|	\p{Nl}++  #letter number
							#|	\p{No}++  #other number


							#sentence end by dot
							|	\. (?=[\x20'
        . "\xc2\xa0"   #U+00A0 [ ] no-break space = non-breaking space
        . '] (?!\p{Ll})  #following symbol not letter in lowercase
									)

							#sentence end by other
							|	(?<!\()    #previous symbol not bracket
								[!?;…]++  #sentence end
								#following symbol not
								(?!["\)'
        . "\xc2\xbb"       #U+00BB [»] right-pointing double angle quotation mark = right pointing guillemet
        . "\xe2\x80\x9d"   #U+201D [”] right double quotation mark
        . "\xe2\x80\x99"   #U+2019 [’] right single quotation mark (and apostrophe!)
        . "\xe2\x80\x9c"   #U+201C [“] left double quotation mark
        . ']
								)
						)
						~sxuSX', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);


    #cleanup
    $words = array();
    $sentences = array();
    $uniques = array();
    $offset_map = array();

    #init
    $abs_pos = 0;  #номер абсолютной позиции слова в тексте
    $w_prev = false;  #предыдущее слово

    foreach ($m as $i => $a)
    {
        $is_alpha = $is_digit = false;
        if ($is_digit = array_key_exists(2, $a)) list($w, $pos) = $a[2];
        elseif ($is_alpha = array_key_exists(1, $a)) list($w, $pos) = $a[1];
        else #delimiter found
        {
            list($w, $pos) = $a[0];
            continue;
        }
        $w_prev = $w;
        $words[$abs_pos] = $w;
        $offset_map[$abs_pos] = $pos;
        $abs_pos++;
    }

    try {
        $uniques = array_count_values(explode(PHP_EOL, UTF8::uppercase(implode(PHP_EOL, $words))));
        ksort($uniques, SORT_REGULAR);
    } catch (Exception $e) {
        die($e->getError());
    }

    //var_dump($words, $uniques);
    //die;
    return $m;
}