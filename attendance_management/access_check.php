<?php
require_once ('./class/db/Base.php');// セッション開始用

function access_check()
{
    $current_page = basename($_SERVER['PHP_SELF']); // 現在のページ名を取得

    // 1. **ログインページはチェック不要**
    if ($current_page === 'page_log_in.php') {
        return true;
    }

    // 2. **ログイン済みのユーザーか確認**
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        $_SESSION['error'] = 'ログイン画面からアクセスしてください。';
        header('Location: page_log_in.php');
        exit();
    }

    // 3. **一般ユーザーが「ユーザー追加ページ」にアクセスしようとした場合**
    if ($current_page === 'page_add_user.php' && $_SESSION['is_admin'] != 1) {
        $_SESSION['error'] = '管理者権限が必要です。';
        header('Location: page_log_in.php');
        exit();
    }

    // **すべてのチェックを通過したら許可**
    return true;
}
?>
