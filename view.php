<?php defined('DICROOT') or die('Доступ запрещён'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="windows-1251">
    <title>Анализатор текста</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="hero-unit<?php if ($text) echo " fold"; ?>">
        <form id="form" action="" method="POST" enctype="multipart/form-data">
            <p><label for="text">Вставьте текст (до 200000 символов) <textarea name="text" id="text" class="input-block-level" cols="150" rows="10" autofocus="autofocus"><?php if (!empty($_POST['text'])) echo $_POST['text']; ?></textarea></label></p>
            <p><label for="file">Или загрузите файл с текстом (до <?php echo $fmsize; ?>Мб / Windows-1251) <input type="file" name="file" id="file" /></label></p>
            <p>
                <label class="checkbox" for="freq"><input type="checkbox" id="freq" name="freq" value="1" checked="checked"> Создать частотный словарь</label>
                <label class="checkbox" for="stop"><input type="checkbox" id="stop" name="stop" value="1"> Создать частотный словарь без <a href="/stop_words_ru.txt" target="_blank" title="Скачать список стоп-слов">стоп-слов</a></label>
                <label class="checkbox" for="morph"><input type="checkbox" id="morph" name="morph" value="1"> Создать частотный словарь с обработкой морфологическим анализатором</label>
                <span class="typeofmorph">
                    <label class="radio inline" for="mtypes"><input type="radio" name="mtype" id="mtypes" value="1" checked="checked"/> mystem <span class="label label-success">рекомендуется</span></label>
                    <label class="radio inline" for="mtypem"><input type="radio" name="mtype" id="mtypem" value="2"/> phpmorphy</label>
                </span>
            </p>
            <p><button type="submit" class="btn btn-primary btn-large"><i class="icon-cog icon-white"></i> Анализировать</button></p>
        </form>
        <div class="more"><button class="btn btn-info btn-small">Другой текст</button></div>
    </div>
    <?php if ($text) { ?>
    <div class="results tabbable tabs-left">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#stat" data-toggle="tab">Статистика</a></li>
            <li><a href="#words" data-toggle="tab">Слова</a></li>
            <li><a href="#stop-words" data-toggle="tab">Стоп-слова</a></li>
            <li><a href="#files" data-toggle="tab">Файлы</a></li>
            <li><a href="#bench" data-toggle="tab">Ресурсы</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="stat">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <td>Символов</td>
                        <td><?php echo $number_of_symbols; ?></td>
                    </tr>
                    <tr>
                        <td>Символов без пробелов</td>
                        <td><?php echo $number_of_symbols - $spaces; ?></td>
                    </tr>
                    <tr>
                        <td>Слов</td>
                        <td><?php echo sizeof($words); ?></td>
                    </tr>
                    <tr>
                        <td>Стоп-слов</td>
                        <td><?php echo sizeof($stop_words_actual); ?></td>
                    </tr>
                    <tr>
                        <td>Уникальных слов</td>
                        <td><?php echo sizeof($dict); ?></td>
                    </tr>
                    <tr>
                        <td>Уникальных слов (без стоп-слов)</td>
                        <td><?php echo sizeof($dict_stop); ?></td>
                    </tr>
                </table>

                <div class="well well-large">
                    <button type="button" class="graph-toggle btn btn-large btn-block" data-toggle="collapse" data-target="#dynamics-holder" data-graph="dict-dynamics">
                        График зависимости размера словаря от длины текста
                    </button>
                    <div id="dynamics-holder" class="collapse">
                        <div id="dict-dynamics"></div>
                    </div>

                    <button type="button" class="graph-toggle btn btn-large btn-block" data-toggle="collapse" data-target="#zipf-holder" data-graph="dict-zipf">
                        График зависимости частоты встречаемости слова от его ранга
                    </button>
                    <div id="zipf-holder" class="collapse">
                        <div id="dict-zipf"></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="words">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <th>Слово</th>
                        <th>Кол-во</th>
                        <th>Частота, %</th>
                    </tr>
                    <?php foreach ($dict_stop as $w => $ws) { ?>
                    <tr>
                        <td><?php echo $w; ?></td>
                        <td><?php echo $ws[0]; ?></td>
                        <td><?php echo $ws[1]; ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="tab-pane" id="stop-words">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <th>Стоп-слово</th>
                        <th>Кол-во</th>
                        <th>Частота, %</th>
                    </tr>
                    <?php foreach ($stop_words_array as $a => $av) { ?>
                    <tr>
                        <td><?php echo $a; ?></td>
                        <td><?php echo $av[0]; ?></td>
                        <td><?php echo $av[1]; ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="tab-pane" id="files">
                <div class="row">
                    <?php
                    if ($files) {
                        foreach ($files as $f => $fv) { ?>
                            <div class="well well-large span3">
                                <a href="<?php echo $fv['file']; ?>" target="_blank"><i class="icon-book"></i> <?php echo $f .' ('. $fv['size'] . ' Кб)' ?></a>
                            </div>
                            <?php }
                    }
                    ?>
                </div>
            </div>
            <div class="tab-pane" id="bench">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <th>Задача</th>
                        <th>Время</th>
                        <th>Память</th>
                    </tr>
                    <?php foreach ($stats as $s => $sv) { ?>
                    <tr>
                        <td><?php echo $s; ?></td>
                        <td><?php echo $sv['time'] . ' с'; ?></td>
                        <td><?php echo $sv['mem'] . ' б'; ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.flot.min.js"></script>
<script type="text/javascript" src="js/jquery.flot.axislabels.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<?php if ($text) { ?>
<script type="text/javascript">
    $(document).ready(function() {
        var dynamics = [
        <?php foreach ($dict_dynamics as $l => $d)
        {
            echo "[{$l}, {$d}],";
        }
        ?>
        ];
        var zipf = [
            <?php foreach ($zipf as $r => $f)
            {
                echo "[{$r}, {$f}],";
            }
            ?>
        ];


        $('.graph-toggle').on('click', function() {
            var self = $(this),
                graph = self.data('graph');

            if (!self.hasClass('builded')) {
                if (self.data('graph') == 'dict-dynamics') {
                    //console.log('build dict-dyn');
                    $.plot('#dict-dynamics', [
                        {
                            data: dynamics,
                            color: '#00adee'
                        }
                    ],
                            {
                                xaxis: {
                                    axisLabel: "длина текста"
                                },
                                yaxis: {
                                    axisLabel: "размер словаря"
                                }
                            }
                    );
                } else if (self.data('graph') == 'dict-zipf') {
                    $.plot('#dict-zipf', [
                        {
                            data: zipf,
                            color: '#eead00'
                        }
                    ],
                            {
                                xaxis: {
                                    axisLabel: "ранг (последовательность слова)"
                                },
                                yaxis: {
                                    axisLabel: "частота слова"
                                }
                            }
                    );
                }
                self.addClass('builded');

            }
        })

    });

</script>
    <?php } ?>
</body>
</html>