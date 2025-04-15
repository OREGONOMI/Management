<?php
// 必要なファイルを読み込む
require_once('./class/db/Attendance.php');
require_once 'access_check.php';
access_check(); // ユーザーがログインしているか確認

header('Location: page_attendance_management.php'); // 保存後に元のページにリダイレクト
// POSTで送信されたデータを取得
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remarks'])) {
  // 現在ログインしているユーザーのID（セッションから取得）
  $user_id = $_SESSION['user_id']; // セッションのユーザーIDに変更してください
  $date = date('Y-m-d'); // 現在の日付を取得

  // 備考を取得
  $remarks = $_POST['remarks'];

  // Attendanceクラスをインスタンス化してupcreateRemarksメソッドを呼び出し
  $attendance = new Attendance();
  $attendance->upcreateRemarks($user_id, $date, $remarks);
  exit();
} else {
  // 不正なリクエストの場合はエラーメッセージ
  $_SESSION['error'] = '備考欄の追加に失敗しました。';
  exit();
}
