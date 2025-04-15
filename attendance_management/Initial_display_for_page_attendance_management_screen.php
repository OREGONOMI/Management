<?php

try {
  //検索ユーザ取得
  //まず管理者かの確認
  if ($_SESSION['is_admin'] == 1) {
    if (empty($_POST['search_user_id'])) {
      $search_user_id = $_SESSION['user_id'];
    } else {
      $search_user_id = $_POST['search_user_id'];
    }
    //一般ユーザの時は開発者モードで値を変更されても当人のデータしか取得できないようにする
  } else if ($_SESSION['is_admin'] == 0) {
    $search_user_id = $_SESSION['user_id'];
  } else {
    // エラーメッセージをセッションに格納
    $_SESSION['error'] = 'ユーザ権限が不明のためデータ取得が出来ません。';
    exit();
  }

  // 編集画面に表示しているユーザのIDを渡すためにIDをSESSION化
  if ($_SESSION['is_admin'] == 1) {
    $_SESSION['search_user_id'] = $search_user_id;
  } else if ($_SESSION['is_admin'] == 0) {
    $_SESSION['search_user_id'] = $_SESSION['user_id'];
  } else {
    // エラーメッセージをセッションに格納
    $_SESSION['error'] = 'ユーザ権限が不明のためデータ取得が出来ません。';
    exit();
  }

  //検索年があるか
  if (empty($_POST['year'])) {
    $search_year = date('Y');
  } else {
    $search_year = $_POST['year'];
  }

  //検索月があるか
  if (empty($_POST['month'])) {
    $search_month = date('m');
  } else {
    $search_month = $_POST['month'];
  }


  // プダウン用ユーザ取得
  try {
    // 権限に合わせてユーザ情報の取得
    require_once('./class/db/User.php');
    $db = new User();
    $user_array = $db->getUserById_and_admin($search_user_id, $_SESSION['is_admin']);
  } catch (Exception $e) {
    // エラーメッセージをセッションに格納
    $_SESSION['error'] = 'ユーザ情報の取得に失敗しました。';
    exit();
  }

  //指定されたユーザID・年・月でその月の勤怠情報を取得
  // 勤怠情報の取得
  require_once('./class/db/Attendance.php');
  $db = new Attendance();

  $work_date = sprintf('%04d-%02d-01', $search_year, $search_month); // 日は適当（ここでは1日）
  $attendance_array = $db->getAttendanceByUser($search_user_id, $work_date, 1);
} catch (Exception $e) {
  // エラーメッセージをセッションに格納
  $_SESSION['error'] = '管理情報の取得に失敗しました。';
  exit();
}
