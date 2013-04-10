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
            <p><label for="text">�������� ����� (�� 200000 ��������) <textarea name="text" id="text" class="input-block-level" cols="150" rows="15" autofocus="autofocus"><?php if (!empty($_POST['text'])) echo $_POST['text']; ?></textarea></label></p>
            <p><label for="file">��� ��������� ���� � ������� (�� <?php echo $fmsize; ?>�� / Windows-1251) <input type="file" name="file" id="file" /></label></p>
            <p>
                <label class="checkbox" for="freq"><input type="checkbox" id="freq" name="freq" value="1" checked="checked"> ������� ��������� �������</label>
                <label class="checkbox" for="stop"><input type="checkbox" id="stop" name="stop" value="1"> ������� ��������� ������� ��� <a href="/stop_words_ru.txt" title="������� ������ ����-����">����-����</a></label>
                <label class="checkbox" for="morph"><input type="checkbox" id="morph" name="morph" value="1"> ������� ��������� ������� � ���������� ��������������� ������������</label>
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
                <div id="dict-dynamics"></div>
                <div id="dict-zipf"></div>
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
                                <a href="<?php echo $fv['file']; ?>"><i class="icon-book"></i> <?php echo $f .' ('. $fv['size'] . ' ��)' ?></a>
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
    $(function() {
        var dynamics = [
            <?php foreach ($dict_dynamics as $l => $d)
            {
                echo "[{$l}, {$d}],";
            }
            ?>
            ],
            zipf = [
            <?php foreach ($zipf as $r => $f)
            {
                echo "[{$r}, {$f}],";
            }
            ?>
            ];

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
    }());

</script>
<?php } ?>
</body>
</html>