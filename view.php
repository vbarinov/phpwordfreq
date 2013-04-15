<?php defined('DICROOT') or die('������ ��������'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="windows-1251">
    <title>���������� ������</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="hero-unit<?php if ($text) echo " fold"; ?>">
        <form id="form" action="" method="POST" enctype="multipart/form-data">
            <p><label for="text">�������� ����� (�� 200000 ��������) <textarea name="text" id="text" class="input-block-level" cols="150" rows="10" autofocus="autofocus"><?php if (!empty($_POST['text'])) echo $_POST['text']; ?></textarea></label></p>
            <p><label for="file">��� ��������� ���� � ������� (�� <?php echo $fmsize; ?>�� / Windows-1251) <input type="file" name="file" id="file" /></label></p>
            <p>
                <label class="checkbox" for="freq"><input type="checkbox" id="freq" name="freq" value="1" checked="checked"> ������� ��������� �������</label>
                <label class="checkbox" for="stop"><input type="checkbox" id="stop" name="stop" value="1"> ������� ��������� ������� ��� <a href="/stop_words_ru.txt" target="_blank" title="������� ������ ����-����">����-����</a></label>
                <label class="checkbox" for="morph"><input type="checkbox" id="morph" name="morph" value="1"> ������� ��������� ������� � ���������� ��������������� ������������</label>
                <span class="typeofmorph">
                    <label class="radio inline" for="mtypes"><input type="radio" name="mtype" id="mtypes" value="1" checked="checked"/> mystem <span class="label label-success">�������������</span></label>
                    <label class="radio inline" for="mtypem"><input type="radio" name="mtype" id="mtypem" value="2"/> phpmorphy</label>
                </span>
            </p>
            <p><button type="submit" class="btn btn-primary btn-large"><i class="icon-cog icon-white"></i> �������������</button></p>
        </form>
        <div class="more"><button class="btn btn-info btn-small">������ �����</button></div>
    </div>
    <?php if ($text) { ?>
    <div class="results tabbable tabs-left">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#stat" data-toggle="tab">����������</a></li>
            <li><a href="#words" data-toggle="tab">�����</a></li>
            <li><a href="#stop-words" data-toggle="tab">����-�����</a></li>
            <li><a href="#files" data-toggle="tab">�����</a></li>
            <li><a href="#bench" data-toggle="tab">�������</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="stat">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <td>��������</td>
                        <td><?php echo $number_of_symbols; ?></td>
                    </tr>
                    <tr>
                        <td>�������� ��� ��������</td>
                        <td><?php echo $number_of_symbols - $spaces; ?></td>
                    </tr>
                    <tr>
                        <td>����</td>
                        <td><?php echo sizeof($words); ?></td>
                    </tr>
                    <tr>
                        <td>����-����</td>
                        <td><?php echo sizeof($stop_words_actual); ?></td>
                    </tr>
                    <tr>
                        <td>���������� ����</td>
                        <td><?php echo sizeof($dict); ?></td>
                    </tr>
                    <tr>
                        <td>���������� ���� (��� ����-����)</td>
                        <td><?php echo sizeof($dict_stop); ?></td>
                    </tr>
                </table>

                <div class="well well-large">
                    <button type="button" class="graph-toggle btn btn-large btn-block" data-toggle="collapse" data-target="#dynamics-holder" data-graph="dict-dynamics">
                        ������ ����������� ������� ������� �� ����� ������
                    </button>
                    <div id="dynamics-holder" class="collapse">
                        <div id="dict-dynamics"></div>
                    </div>

                    <button type="button" class="graph-toggle btn btn-large btn-block" data-toggle="collapse" data-target="#zipf-holder" data-graph="dict-zipf">
                        ������ ����������� ������� ������������� ����� �� ��� �����
                    </button>
                    <div id="zipf-holder" class="collapse">
                        <div id="dict-zipf"></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="words">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <th>�����</th>
                        <th>���-��</th>
                        <th>�������, %</th>
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
                        <th>����-�����</th>
                        <th>���-��</th>
                        <th>�������, %</th>
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
                                <a href="<?php echo $fv['file']; ?>" target="_blank"><i class="icon-book"></i> <?php echo $f .' ('. $fv['size'] . ' ��)' ?></a>
                            </div>
                            <?php }
                    }
                    ?>
                </div>
            </div>
            <div class="tab-pane" id="bench">
                <table class="table table-bordered table-hover table-condensed">
                    <tr>
                        <th>������</th>
                        <th>�����</th>
                        <th>������</th>
                    </tr>
                    <?php foreach ($stats as $s => $sv) { ?>
                    <tr>
                        <td><?php echo $s; ?></td>
                        <td><?php echo $sv['time'] . ' �'; ?></td>
                        <td><?php echo $sv['mem'] . ' �'; ?></td>
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
                                    axisLabel: "����� ������"
                                },
                                yaxis: {
                                    axisLabel: "������ �������"
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
                                    axisLabel: "���� (������������������ �����)"
                                },
                                yaxis: {
                                    axisLabel: "������� �����"
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