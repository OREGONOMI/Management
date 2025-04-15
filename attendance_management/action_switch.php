<?php
// DB接続設定（DBクラスを利用）
require_once('./class/db/Attendance.php');
require_once('./class/db/Breaks.php');
$db = new Attendance();
$break = new Breaks();

// ユーザーIDと勤務日を取得
$user_id = $_SESSION['user_id'];
$work_date = date('Y-m-d');

// アクションによる処理分岐
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        // 始業ボタンの処理
        case 'start_time':
            // 現在の時刻を出勤時間として記録
            $start_time = date('Y-m-d H:i');
            $result = $db->upcreateStartTime($user_id, $work_date, $start_time);
            break;

        // 終業ボタンの処理
        case 'end_time':
            // 現在の時刻を退勤時間として記録
            $end_time = date('Y-m-d H:i');
            $result = $db->upcreateEndTime($user_id, $work_date, $end_time);
            // 休憩終了時間がない場合のBreaksテーブルを更新
            $break_info = $break->getBreaksByAttendance($_SESSION['attendance_id']);
            $total_break_seconds = 0; // 総休憩時間（秒単位）

            foreach ($break_info as $break_entry) {
                if (!empty($break_entry['break_start_time']) && !empty($break_entry['break_end_time'])) {
                    // DateTime に変換
                    $break_start = new DateTime($break_entry['break_start_time']);
                    $break_end = new DateTime($break_entry['break_end_time']);
            
                    // 差分を計算（DateIntervalオブジェクト）
                    $interval = $break_start->diff($break_end);
            
                    // 差分の時間と分を秒に換算して合計
                    $diff_seconds = ($interval->h * 3600) + ($interval->i * 60);
            
                    // 休憩時間を累積
                    $total_break_seconds += $diff_seconds;
                }
            }
            
            // 総休憩時間を「hh:mm:ss」形式に変換
            $total_break_time = gmdate("H:i:s", $total_break_seconds);
            // 休憩時間の記録
            $db->recordBreakTime($user_id, $work_date, $total_break_time);

            // (休憩ー休憩終了)＋n(休憩ー休憩終了)を計算して休憩時間を入れる

            break;

        // 休憩ボタンの処理
        case 'break_start_time':
            // 現在の時刻を退勤時間として記録
            $break_start_time = date('Y-m-d H:i');
            $result = $break->createBreakStartTime($_SESSION['attendance_id'], $break_start_time);
            break;

        // 休憩終了ボタンの処理
        case 'break_end_time':
            // 現在の時刻を退勤時間として記録
            $break_end_time = date('Y-m-d H:i');
            $result = $break->updateBreakEndTime($_SESSION['attendance_id'], $break_end_time);
            break;

        // その他のアクション
        default:
            $_SESSION['error'] = '無効なアクションです。';
            break;
    }
}

// リダイレクト
header('Location: page_attendance_management.php');
exit();
