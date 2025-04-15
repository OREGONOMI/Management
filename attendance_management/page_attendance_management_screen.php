<!-- http://localhost/attendance_management/page_attendance_management_screen.php -->
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
    <title>勤怠管理画面</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ヘッダー -->
    <?php include "common_header.php" ?>

    <!-- 情報取得表示 -->
    <?php include "Initial_display_for_page_attendance_management_screen.php" ?>

    <main class="space_for_management_screen">

        <!-- エラーメッセージ欄 -->
        <?php
        if (isset($_SESSION['error'])) {
            // エラーメッセージを表示
            echo "<p class='error'>" . htmlspecialchars($_SESSION['error']) . "</p>";
            // エラーメッセージを表示した後にセッションから削除
            unset($_SESSION['error']);
        }
        ?>

        <!-- 検索情報入力のボタンスクリプト -->

        <!-- 検索情報入力 -->
        <form method="POST" action="page_attendance_management_screen.php">
            <!-- 社員情報入力 -->
            <div class="common_item_box">
                <div class="employee_id">
                    <label for="employee-name">社員名</label>
                    <select class="common_item_box_select" id="employee-name" name="search_user_id">
                        <?php
                        foreach ($user_array as $user) {
                            echo "<option value='" . htmlspecialchars($user['id']) . "'>" . htmlspecialchars($user['family_name']) . " " . htmlspecialchars($user['first_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="employee_id">社員番号<?= $user_array[0]['id'] ?></div>
            </div>

            <!-- 年月選択 -->
            <?php
            $year = isset($_POST['year']) && !empty($_POST['year']) ? $_POST['year'] : date('Y');
            $month = isset($_POST['month']) && !empty($_POST['month']) ? $_POST['month'] : date('m');
            ?>

            <div class="common_item_box">
                <!-- 年選択 -->
                <input class="common_item_box_select" type="number" id="year" name="year" value="<?= htmlspecialchars($year) ?>"
                    min="2000" max="2100" step="1" placeholder="YYYY">
                <span>年</span>

                <!-- 月選択 -->
                <select class="common_item_box_select" id="month" name="month">
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $selected = ($month == $i) ? 'selected' : '';  // $_POST['month'] が空なら今日の月を設定
                        echo "<option value='$i' $selected>" . sprintf('%02d', $i) . "</option>";
                    }
                    ?>
                </select>
                <span>月</span>
            </div>


            <!-- 検索ボタン（最初は非表示） -->
            <button class="remarks_btn" type="submit" id="search-btn" style="display: none;">検索</button>
        </form>

        <script>
            // 全てのセレクトボックスと入力を取得
            const selects = document.querySelectorAll("select");
            const yearInput = document.getElementById("year");
            const searchBtn = document.getElementById("search-btn");

            // 年と月が変更された場合に検索ボタンを表示
            yearInput.addEventListener("input", function() {
                searchBtn.style.display = "block";
            });

            selects.forEach(select => {
                select.addEventListener("change", function() {
                    searchBtn.style.display = "block";
                });
            });
        </script>


        <!-- 勤怠データ -->
        <?php
        // 年月の取得（POSTデータがない場合は当月を使用）
        $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
        $month = isset($_POST['month']) ? $_POST['month'] : date('m');

        // 月の日数を取得
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // 勤怠データを日付ごとに整理
        $attendance_by_date = [];
        foreach ($attendance_array as $attendance) {
            $day = date('d', strtotime($attendance['work_date']));
            $attendance_by_date[$day] = $attendance;
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>始業<br>時間</th>
                    <th>休憩<br>時間</th>
                    <th>終業<br>時間</th>
                    <th>合計<br>時間</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                    <?php
                    $day_str = sprintf('%02d', $day);
                    $attendance = $attendance_by_date[$day_str] ?? null;
                    $row_class = $attendance ? "" : "class='no-data'"; // データがない場合にクラスを付与
                    $work_date = $year . '-' . sprintf('%02d', $month) . '-' . $day_str;
                    $attendance_id = $attendance['id'] ?? 'new'; // データがない場合は "new"
                    ?>

                    <tr <?= $row_class ?>>
                        <td><a href="page_attendance_edit.php?id=<?= htmlspecialchars($attendance_id) ?>&date=<?= htmlspecialchars($work_date) ?>" style="display: block; text-decoration: none; color: inherit;"><?= htmlspecialchars($day_str) ?></a></td>
                        <td><?= $attendance && !empty($attendance['start_time']) ? date("H:i", strtotime($attendance['start_time'])) : "未入力" ?></td>
                        <td><?= $attendance && !empty($attendance['break_time']) ? substr($attendance['break_time'], 0, 5) : "--:--" ?></td>
                        <td><?= $attendance && !empty($attendance['end_time']) ? date("H:i", strtotime($attendance['end_time'])) : "未入力" ?></td>
                        <td>
                            <?php
                            if ($attendance && !empty($attendance['start_time']) && !empty($attendance['end_time'])) {
                                $start_time = new DateTime($attendance['start_time']);
                                $end_time = new DateTime($attendance['end_time']);
                                $break_seconds = 0;
                                if (!empty($attendance['break_time'])) {
                                    list($h, $i) = explode(':', $attendance['break_time']);
                                    $break_seconds = ($h * 3600) + ($i * 60);
                                }
                                $total_seconds = max(0, $end_time->getTimestamp() - $start_time->getTimestamp() - $break_seconds);
                                echo sprintf('%02d:%02d', floor($total_seconds / 3600), floor(($total_seconds % 3600) / 60));
                            } else {
                                echo "--:--";
                            }
                            ?>
                        </td>
                    </tr>

                <?php endfor; ?>
            </tbody>
        </table>

        <!-- ボタン -->
        <div class="action_button_box">
            <button class="common_item_box_button" onclick="history.back()">戻る</button>
            <form class="form_CSV" method="POST" action="CSVexport.php">
                <input type="hidden" name="search_user_id" value="<?= htmlspecialchars($search_user_id) ?>">
                <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                <button class="common_item_box_button">CSV</button>
            </form>
        </div>
    </main>
</body>

</html>