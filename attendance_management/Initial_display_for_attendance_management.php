<?php
try {
  require_once('./class/db/Attendance.php');
  $db = new Attendance();
  $work_date = date('Y-m-d'); // 当日の日付を取得
  $info = $db->getAttendanceByUser($_SESSION['user_id'],$work_date,0);

  // データがある場合は最初のレコードを取得、なければ空配列をセット
  $attendance = (!empty($info) && isset($info[0])) ? $info[0] : [];

  // 各値を取得（データがない場合は '' をセット）
  $start_time = isset($attendance['start_time']) && strtotime($attendance['start_time']) ? date('H:i', strtotime($attendance['start_time'])) : '';
  $end_time = isset($attendance['end_time']) && strtotime($attendance['end_time']) ? date('H:i', strtotime($attendance['end_time'])) : '';
  $break_start_time = isset($attendance['break_start_time']) && strtotime($attendance['break_start_time']) ? date('H:i', strtotime($attendance['break_start_time'])) : '';
  $break_end_time = isset($attendance['break_end_time']) && strtotime($attendance['break_end_time']) ? date('H:i', strtotime($attendance['break_end_time'])) : '';

  // 勤怠IDの確認
  if (!isset($attendance['id']) || empty($attendance['id'])) {
      $_SESSION['sign'] = '勤怠情報なし';
  } else {
      $_SESSION['attendance_id'] = $attendance['id'];
      //休憩時間取得
      require_once('./class/db/Breaks.php');
      $db = new Breaks();
      $breaks_info = $db->getBreaksByAttendance($_SESSION['attendance_id']);
  }
} catch (Exception $e) {
  // エラーメッセージをセッションに格納
  $_SESSION['error'] = '管理情報の取得に失敗しました。';
  // ログインページにリダイレクト
  header('Location: page_attendance_management.php');
  exit();
}
