<!-- http://localhost/attendance_management/page_attendance_management.php -->
<?php
// 正規ログインチェック
require_once 'access_check.php';
access_check();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠記録</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ヘッダー -->
    <?php include "common_header.php" ?>

    <main class=space>
        <div class="index">
            <!-- 時間取得表示 -->
            <div class="dis_time">
                <span id="real-time"></span>
                <script type="text/javascript">
                    function Time() {
                        var realTime = new Date();
                        var hour = realTime.getHours().toString().padStart(2, '0'); // 2桁に変換
                        var minutes = realTime.getMinutes().toString().padStart(2, '0'); // 2桁に変換
                        var text = hour + ":" + minutes;
                        document.getElementById("real-time").innerHTML = text;
                    }
                    setInterval(Time, 1000);
                    Time(); // 初回実行
                </script>
            </div>

            <!-- 月日曜日取得表示 -->
            <div class="common_item_box">
                <div class="employee_id">
                    <span id="view_today"></span>

                    <script type="text/javascript">
                        document.getElementById("view_today").innerHTML = getToday();

                        function getToday() {
                            var now = new Date();
                            var mon = now.getMonth() + 1; //１を足すこと
                            var day = now.getDate();
                            var you = now.getDay(); //曜日(0～6=日～土)

                            //曜日の選択肢
                            var youbi = new Array("日", "月", "火", "水", "木", "金", "土");
                            //出力用
                            var s = mon + "/" + day + " (" + youbi[you] + ")";
                            return s;
                        }
                    </script>
                </div>
            </div>

            <!-- 情報取得表示 -->
            <?php include "Initial_display_for_attendance_management.php" ?>

            <!-- エラーメッセージ欄 -->
            <?php
            if (isset($_SESSION['error'])) {
                // エラーメッセージを表示
                echo "<p class='error'>" . htmlspecialchars($_SESSION['error']) . "</p>";
                // エラーメッセージを表示した後にセッションから削除
                unset($_SESSION['error']);
            }
            ?>
            <!-- 備考欄保存のボタンスクリプト -->
            <script type="text/javascript">
                window.addEventListener('DOMContentLoaded', function() {
                    let textarea_contact = document.getElementById("remarks");
                    let saveButton = document.getElementById("saveButton");

                    // フォーカスが外れたら保存ボタンを表示
                    textarea_contact.addEventListener("blur", function() {
                        if (textarea_contact.value.trim() !== "") { // 空欄でなければ表示
                            saveButton.style.display = "block";
                        }
                    });

                    // フォーム送信後、ボタンを非表示にする
                    document.getElementById("remarksForm").addEventListener("submit", function() {
                        saveButton.style.display = "none";
                    });
                });
            </script>

            <!-- 備考欄 -->
            <form id="remarksForm" action="remarks.php" method="POST">
                <textarea class="common_item_box_input remarks" name="remarks" id="remarks" placeholder="備考コメント">
<?= isset($attendance['remarks']) ? htmlspecialchars($attendance['remarks'], ENT_QUOTES, 'UTF-8') : '' ?>
</textarea>

                <button type="submit" id="saveButton" class="remarks_btn" style="display: none;">備考欄を保存</button>
            </form>

            <!-- ログ情報がない場合のメッセージ欄 -->
            <?php
            if (isset($_SESSION['sign'])) {
                // エラーメッセージを表示
                echo "<p>" . htmlspecialchars($_SESSION['sign']) . "</p>";
                // エラーメッセージを表示した後にセッションから削除
                unset($_SESSION['sign']);
            }
            ?>
            <div class="log-box">
                <?php if ($start_time !== ''): ?>
                    <div class="log_work">
                        <span class="time"><?= htmlspecialchars($start_time) ?></span>
                        <span class="status">始業</span>
                    </div>
                <?php endif; ?>

                <!-- 取得した分だけの休憩時間を休憩・休憩終了の順番に表示を繰り返す -->
                <?php if (!empty($breaks_info)): ?>
                    <?php
                    // 休憩開始時間と終了時間を順番に対応させて表示するための処理
                    foreach ($breaks_info as $break): ?>
                        <?php
                        $break_start_time = isset($break['break_start_time']) && strtotime($break['break_start_time']) ? date('H:i', strtotime($break['break_start_time'])) : '';
                        $break_end_time = isset($break['break_end_time']) && strtotime($break['break_end_time']) ? date('H:i', strtotime($break['break_end_time'])) : '';
                        ?>

                        <!-- 休憩開始時間の表示 -->
                        <?php if ($break_start_time !== ''): ?>
                            <div class="log_break">
                                <span class="time"><?= htmlspecialchars($break_start_time) ?></span>
                                <span class="status">休憩</span>
                            </div>
                        <?php endif; ?>

                        <!-- 休憩終了時間の表示 -->
                        <?php if ($break_end_time !== ''): ?>
                            <div class="log_work">
                                <span class="time"><?= htmlspecialchars($break_end_time) ?></span>
                                <span class="status">休憩終了</span>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($end_time !== ''): ?>
                    <div class="log_break">
                        <span class="time"><?= htmlspecialchars($end_time) ?></span>
                        <span class="status">終業</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="common_item_box status_box">
            <!-- ボタンの表示制御 -->
            <div class="status_button_box">
                <!-- 始業ボタン -->
                <?php if ($start_time === ''): ?>
                    <form action="action_switch.php" method="POST">
                        <input type="hidden" name="action" value="start_time">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="work_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn">始業</button>
                    </form>
                <?php else: ?>
                    <button class="btn_disabled">始業</button>
                <?php endif; ?>

                <!-- 休憩ボタン -->
                <?php if (($start_time !== '' && $break_start_time === '' && $end_time === '') || ($break_start_time !== '' && $break_end_time !== '' && $end_time === '')): ?>
                    <form action="action_switch.php" method="POST">
                        <input type="hidden" name="action" value="break_start_time">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="work_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn">休憩</button>
                    </form>
                <?php else: ?>
                    <button class="btn_disabled">休憩</button>
                <?php endif; ?>

                <!-- 休憩終了ボタン -->
                <?php if ($break_start_time !== '' && $break_end_time === ''): ?>
                    <form action="action_switch.php" method="POST">
                        <input type="hidden" name="action" value="break_end_time">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="work_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn">休憩終了</button>
                    </form>
                <?php else: ?>
                    <button class="btn_disabled">休憩終了</button>
                <?php endif; ?>

                <!-- 終業ボタン -->
                <?php if ($start_time !== '' && $break_end_time !== '' && $end_time === ''): ?>
                    <form action="action_switch.php" method="POST">
                        <input type="hidden" name="action" value="end_time">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="work_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn">終業</button>
                    </form>
                <?php else: ?>
                    <button class="btn_disabled">終業</button>
                <?php endif; ?>
            </div>
        </div>

    </main>
</body>

</html>