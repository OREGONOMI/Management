<?php
// DB接続設定（DBクラスを利用）
require_once('./class/db/Attendance.php');
require_once('./class/db/Breaks.php');
$db = new Attendance();
$break = new Breaks();

// ユーザーIDと勤務日を取得
$user_id = $_SESSION['search_user_id'];
$work_date = $_SESSION['date_for_edit'];

//まず管理者かの確認
if ($_SESSION['is_admin'] == 1) {
    if (empty($user_id)) {
        $search_user_id = $_SESSION['user_id'];
    } else {
        $search_user_id = $user_id;
    }
    //一般ユーザの時は開発者モードで値を変更されても当人のデータしか取得できないようにする
} else if ($_SESSION['is_admin'] == 0) {
    $search_user_id = $_SESSION['user_id'];
} else {
    // エラーメッセージをセッションに格納
    $_SESSION['error'] = 'ユーザ権限が不明のためデータ取得が出来ません。';
    exit();
}

//始業時間を新規保存or更新
$db->upcreateStartTime($user_id, $work_date, $_POST['start_time_for_edit']);
//終業時間を新規保存or更新
$db->upcreateEndTime($user_id, $work_date, $_POST['end_time_for_edit']);
//終業時間を新規保存or更新
$db->upcreateRemarks($user_id, $work_date, $_POST['remarks_for_edit']);
// 休憩終了時間がない場合のBreaksテーブルを更新
$break_info = $break->getBreaksByAttendance($_SESSION['attendance_id_for_edit']);
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

//⚠新規保存時にAttendance_idを取得してかた休憩時間を保存するようにしたい 
$attendance_id_for_break = $db->getAttendanceByUser($search_user_id, $work_date,0);
$_SESSION['attendance_id_for_edit'] = $attendance_id_for_break[0]['id'];

// 休憩時間の保存処理
// 既存の休憩データを削除
$break->deleteBreak($_SESSION['attendance_id_for_edit']);
// 休憩時間の保存
$break_start_times = $_POST['break_start_time'] ?? [];
$break_end_times = $_POST['break_end_time'] ?? [];

$total_break_seconds = 0; // 総休憩時間（秒）

for ($i = 0; $i < count($break_start_times); $i++) {
    if (!empty($break_start_times[$i]) && !empty($break_end_times[$i])) {
        // 休憩時間の登録
        $break->createBreakTime($_SESSION['attendance_id_for_edit'], $break_start_times[$i], $break_end_times[$i]);

        // 休憩時間の計算
        $break_start = new DateTime($break_start_times[$i]);
        $break_end = new DateTime($break_end_times[$i]);
        $interval = $break_start->diff($break_end);
        $diff_seconds = ($interval->h * 3600) + ($interval->i * 60);
        $total_break_seconds += $diff_seconds;
    }
}

// 総休憩時間を「hh:mm:ss」形式に変換
$total_break_time = gmdate("H:i:s", $total_break_seconds);
// 勤怠管理側の休憩時間の記録
$db->recordBreakTime($user_id, $work_date, $total_break_time);

// リダイレクト
header('Location: page_attendance_management_screen.php');
exit();
