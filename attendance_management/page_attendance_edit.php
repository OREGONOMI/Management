<!-- http://localhost/attendance_management/page_attendance_edit.php -->
<?php
// 正規ログインチェック
require_once 'access_check.php'; // ログインチェック
require_once('./class/db/Attendance.php');
require_once('./class/db/Breaks.php');
access_check();

$attendance_id = $_GET['id'] ?? 'new'; // IDがなければ "new" とする
//編集画面に渡す$attendance_idを生成
$_SESSION['attendance_id_for_edit'] = $attendance_id;
$date = $_GET['date'] ?? date('Y-m-d'); // 日付がない場合は今日の日付
//編集画面に渡す$attendance_idを生成
$_SESSION['date_for_edit'] = $date;

// 既存データの取得
if ($attendance_id === 'new') {
    $attendance = [
        'id' => 'new',
        'work_date' => $date,
        'start_time' => $date,
        'end_time' => $date,
        'breaks' => []
    ];
} else if ($attendance_id !== 'new') {
    $db = new Attendance();
    $result_attendance = $db->getAttendanceByID($attendance_id);
    $attendance = $result_attendance;
    // 休憩時間の取得
    $db_b = new Breaks();
    $result_break = $db_b->getBreaksByAttendance($attendance_id);
    $break = $result_break;
} else {
    $_SESSION['error'] = "データが見つかりませんでした。";
    header("Location: page_attendance_management_screen.php");
    exit();
}

// フォーム送信時の処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $start_time = $_POST['start_time'] ?: null;
    $end_time = $_POST['end_time'] ?: null;

    $break_start_times = $_POST['break_start_time'] ?? [];
    $break_end_times = $_POST['break_end_time'] ?? [];

    if ($attendance_id === 'new') {
        // 新規作成
        $sql = "INSERT INTO attendance (work_date, start_time, end_time) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date, $start_time, $end_time]);

        $attendance_id = $pdo->lastInsertId();
    } else {
        // 更新処理
        $sql = "UPDATE attendance SET start_time = ?, end_time = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$start_time, $end_time, $attendance_id]);

        // 既存の休憩時間を削除
        $sql = "DELETE FROM breaks WHERE attendance_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$attendance_id]);
    }

    // 休憩時間の保存
    for ($i = 0; $i < count($break_start_times); $i++) {
        if (!empty($break_start_times[$i]) && !empty($break_end_times[$i])) {
            $sql = "INSERT INTO breaks (attendance_id, break_start_time, break_end_time) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$attendance_id, $break_start_times[$i], $break_end_times[$i]]);
        }
    }

    header("Location: page_attendance_management_screen.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠編集</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ヘッダー -->
    <?php include "common_header.php"; ?>

    <main>
        <div class="common_item_box">
            <!-- エラーメッセージ欄 -->
            <?php
            if (isset($_SESSION['error'])) {
                // エラーメッセージを表示
                echo "<p class='error'>" . htmlspecialchars($_SESSION['error']) . "</p>";
                // エラーメッセージを表示した後にセッションから削除
                unset($_SESSION['error']);
            }
            ?>

            <?php
            echo $date;
            if ($attendance_id === 'new') {
                echo '<h1 class="h2_edit">新規作成</h1>';
            } else {
                echo '<h1 class="h2_edit">勤怠編集</h1>';
            }
            ?>

            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form class="form_min_width" method="POST" action="action_switch_for_edit.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($attendance['id']) ?>">
                <input type="hidden" name="work_date" value="<?= htmlspecialchars($attendance['work_date']) ?>">

                <!-- 勤怠時間 -->
                <div class="form-group">
                    <label for="start_time">始業時間</label>
                    <input type="datetime-local" class="time_edit" id="start_time" name="start_time_for_edit"
                        value="<?= isset($attendance['start_time']) ? date('Y-m-d\TH:i', strtotime($attendance['start_time'])) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time">終業時間</label>
                    <input type="datetime-local" class="time_edit" id="end_time" name="end_time_for_edit"
                        value="<?= isset($attendance['end_time']) ? date('Y-m-d\TH:i', strtotime($attendance['end_time'])) : '' ?>" required>
                </div>

                <!-- 休憩時間 -->
                <h2 class="h2_edit">休憩時間</h2>
                <div id="break-container">
                    <?php if (!empty($break)): ?>
                        <?php foreach ($break as $b): ?>
                            <div class="break-row">
                                <div>休憩開始<input type="datetime-local" class="time_edit" name="break_start_time[]"
                                        value="<?= isset($b['break_start_time']) ? date('Y-m-d\TH:i', strtotime($b['break_start_time'])) : '' ?>"></div>
                                <div>休憩終了<input type="datetime-local" class="time_edit" name="break_end_time[]"
                                        value="<?= isset($b['break_end_time']) ? date('Y-m-d\TH:i', strtotime($b['break_end_time'])) : '' ?>"></div>
                                <button type="button" class="remove-break">削除</button>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>

                <button type="button" class="common_item_box_button add_break" id="add-break">休憩追加</button>
                <!-- ボタン -->

                <!-- 備考欄 -->
                <h2 class="h2_edit">備考欄</h2>
                <textarea class="common_item_box_input remarks_edit" name="remarks_for_edit" placeholder="備考コメント">
<?= isset($attendance['remarks']) ? htmlspecialchars($attendance['remarks'], ENT_QUOTES, 'UTF-8') : '' ?>
</textarea>

                <div class="action_button_box">
                    <button type="submit" class="common_item_box_button">保存</button>
                    <a class="common_item_box_button looks_like_a_button" href="./page_attendance_management_screen.php">戻る</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('add-break').addEventListener('click', function() {
            var container = document.getElementById('break-container');
            var newRow = document.createElement('div');
            newRow.classList.add('break-row');
            newRow.innerHTML = `
                <div>休憩開始<input type="datetime-local" class="time_edit" name="break_start_time[]" required></div>
                <div>休憩終了<input type="datetime-local" class="time_edit" name="break_end_time[]" required></div>
                <button type="button" class="remove-break">削除</button>
            `;
            container.appendChild(newRow);

            newRow.querySelector('.remove-break').addEventListener('click', function() {
                this.parentNode.remove();
            });
        });

        document.querySelectorAll('.remove-break').forEach(button => {
            button.addEventListener('click', function() {
                this.parentNode.remove();
            });
        });
    </script>
</body>

</html>