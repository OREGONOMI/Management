<?php
// 正規ログインチェック
require_once 'access_check.php';
access_check();

try {
  // 検索ユーザ取得（管理者と一般ユーザで処理を分岐）
  if ($_SESSION['is_admin'] == 1) {
      $search_user_id = !empty($_POST['search_user_id']) ? $_POST['search_user_id'] : $_SESSION['user_id'];
  } else {
      $search_user_id = $_SESSION['user_id'];
  }

  // 年と月の取得（POSTデータがない場合は当月を使用）
  $search_year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
  $search_month = isset($_POST['month']) ? sprintf('%02d', (int)$_POST['month']) : date('m');

  // 勤務日（YYYY-MM形式）
  $work_date = "{$search_year}-{$search_month}";

  require_once('./class/db/CSVExporter.php');
  $csvExporter = new CSVExporter();
  $csvExporter->exportToCSV($search_user_id, $work_date);
  exit(); // ここで終了する（CSV出力後に余計なHTMLを出力しないように）
} catch (Exception $e) {
  $_SESSION['error'] = 'CSV出力処理の起動に失敗しました。';
  header("Location: page_attendance_management_screen.php"); // エラー時に画面へリダイレクト
  exit();
}
