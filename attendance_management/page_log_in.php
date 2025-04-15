<!-- http://localhost/attendance_management/page_log_in.php -->
<?php
// 正規ログインチェック
require_once 'access_check.php';
access_check();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ヘッダー -->
    <?php include "common_header.php" ?>

    <main>
        <div class="common_item_box">
            <!-- セッションリセット -->
            <?php
            //URLで打ち込まれた場合弾くため正規アクセス確認後にフラグ削除
            unset($_SESSION['login']);
            unset($_SESSION['user_id']);
            unset($_SESSION['is_admin']);
            ?>
            <!-- エラーメッセージ欄 -->
            <?php
            if (isset($_SESSION['error'])) {
                // エラーメッセージを表示
                echo "<p class='error'>" . htmlspecialchars($_SESSION['error']) . "</p>";
                // エラーメッセージを表示した後にセッションから削除
                unset($_SESSION['error']);
            }
            ?>
            <form class="common_form_space" action="log_in.php" method="POST">
                <input class="common_item_box_input no-spin" type="number" name="employee_id" placeholder="社員番号" required  onkeydown="return event.keyCode !== 69">
                <div class="password_box">
                    <input class="common_item_box_input" type="password" name="password" placeholder="パスワード" required id="password">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        ー
                    </button>
                </div>

                <script>
                    function togglePassword() {
                        const passwordInput = document.getElementById("password");
                        const toggleButton = document.querySelector(".toggle-password");

                        if (passwordInput.type === "password") {
                            passwordInput.type = "text";
                            toggleButton.textContent = "👁️"; // 目を開いたアイコン
                        } else {
                            passwordInput.type = "password";
                            toggleButton.textContent = "ー"; // 目を閉じたアイコン
                        }
                    }
                </script>
                <button class="common_item_box_button" type="submit">ログイン</button>
            </form>
        </div>
    </main>

    <footer>
        <p>Created in JS</p>
    </footer>
</body>

</html>