<?php
try {
  require_once('./class/db/User.php');

  if (isset($_POST['employee_id']) && isset($_POST['password'])) {
    // 入力されたログイン情報
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    $db = new User();
    $user = $db->getUserById($employee_id); //アクセス権限を所持しているアカウントであるかチェック

    // ユーザー確認とパスワード照合
    if ($user['is_deleted'] == 0 && password_verify($password, $user['password'])) {
      //正規ログイン許可フラグ
      $_SESSION['login'] = true;
      //ユーザーID取得
      $_SESSION['user_id'] = $user['id'];
      //権限
      $_SESSION['is_admin'] = $user['is_admin'];
      //TODOリストアクセス
      header('Location: page_attendance_management.php');
      exit();
    }

    // 存在しない社員番号を打ち込まれたまたはパスワード不一致でcatchを動かす
    throw new Exception();
  } else {
    // 入力情報なしの際にもcatchを動かす
    throw new Exception();
  }
} catch (Exception $e) {
  // エラーメッセージをセッションに格納
  $_SESSION['error'] = '社員番号またはパスワードが違います。';
  // ログインページにリダイレクト
  header('Location: page_log_in.php');
  exit();
}
