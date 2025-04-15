<!-- http://localhost/attendance_management/page_add_user.php -->
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
    <title>ユーザー追加</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- ヘッダー -->
    <?php include "common_header.php" ?>

    <main>
        <!-- 画面切り替え -->
        <?php
        $registered_user = $_SESSION['registered_user'] ?? null;
        unset($_SESSION['registered_user']); // 表示後にセッション情報を削除
        ?>

        <div class="common_item_box">
            <!-- エラーメッセージ欄 -->
            <?php
            if (isset($_SESSION['error'])) {
                // エラーメッセージを表示
                echo "<p class='error'>" . htmlspecialchars($_SESSION['error']) . "</p>";
                // エラーメッセージを表示した後にセッションから削除
                unset($_SESSION['error']);
            }
            ?>
            <!-- 社員登録時 -->
            <?php if ($registered_user === null): ?>
                <form class="common_form_space" action="./add_user.php" method="POST">
                    <p>社員番号は登録後に自動生成されます</p>
                    <input class="common_item_box_input" type="text" name="family_name" placeholder="姓" required>
                    <input class="common_item_box_input" type="text" name="first_name" placeholder="名" required>
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
                    <div>
                        <input type="radio" id="normal" name="is_admin" value=0 checked />
                        <label for="normal">一般社員</label>
                        <input type="radio" id="admin" name="is_admin" value=1 />
                        <label for="admin">管理者</label>
                    </div>
                    <!-- ボタン -->
                    <div class="action_button_box">
                        <button class="common_item_box_button" onclick="history.back()">戻る</button>
                        <button class="common_item_box_button">登録</button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- 社員登録後 -->
            <?php if ($registered_user): ?>
                <div class="registration_results">
                    <p>社員登録が完了致しました</p>
                    <p>社員番号：<span id="employee_id"><?= htmlspecialchars($registered_user['employee_id']) ?></span></p>
                    <p>社員名：<span id="employee_name"><?= htmlspecialchars($registered_user['employee_name']) ?></span></p>
                    <p>パスワード：<span id="password"><?= htmlspecialchars($registered_user['password']) ?></span></p>
                    <button class="common_item_box_button" onclick="copyToClipboard()">コピーする</button>
                </div>
            <?php endif; ?>
            <!-- コピー機能 -->
            <script>
                function copyToClipboard() {
                    // コピーするテキストを取得
                    const employeeId = document.getElementById("employee_id").textContent;
                    const employeeName = document.getElementById("employee_name").textContent;
                    const password = document.getElementById("password").textContent;

                    // テキストを作成
                    const textToCopy = `社員番号: ${employeeId}\n社員名: ${employeeName}\nパスワード: ${password}`;

                    // 一時的なtextareaを作成
                    const textarea = document.createElement("textarea");
                    textarea.value = textToCopy;
                    document.body.appendChild(textarea);

                    // テキストを選択してコピー
                    textarea.select();
                    document.execCommand("copy");

                    // 一時的な要素を削除
                    document.body.removeChild(textarea);

                    // コピー完了のアラート
                    alert("社員番号とパスワードをコピーしました");
                }
            </script>

        </div>
    </main>
</body>

</html>