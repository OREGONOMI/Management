<?php
try {
  require_once('./class/db/User.php');
  
  // フォームデータの取得
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $family_name = $_POST['family_name'];
    $first_name = $_POST['first_name'];
    $password = $_POST['password'];
    $is_admin = $_POST['is_admin'];
    //新規ユーザ追加処理
    $db = new User();
    $user_id = $db->getUserIdIfExistsByPFF($password, $family_name, $first_name);
    //ユーザ名とパスワードの組み合わせの確認(IDの検索が出来なくなるのを防ぐ→ユーザ追加画面でID取得を使用している)
    if ($user_id != null) {
      // ユーザーが既に存在する場合
      $_SESSION['error'] = 'このユーザ名とパスワードの組み合わせは既に登録済みです';
    } else {
      // 新規作成
      $db->createUser($password, $family_name, $first_name, $is_admin);
      // 社員番号の取得
      $employee_id = $db->getUserIdIfExistsByPFF($password, $family_name, $first_name);
      // 登録成功時の情報をセッションに保存
      $_SESSION['registered_user'] = [
        'employee_id' => $employee_id,
        'employee_name' => $family_name . ' ' . $first_name,
        'password' => $password
      ];
    }
  }
  header('Location: page_add_user.php');
  exit();
} catch (Exception $e) {
  // ログインページにリダイレクト
  header('Location: error.php');
  exit();
}
