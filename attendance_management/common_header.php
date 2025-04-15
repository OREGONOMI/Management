<header>
    <h1>勤怠管理</h1>

    <?php if (basename($_SERVER['PHP_SELF']) !== 'page_log_in.php') : ?>
        <!-- ハンバーガーメニュー部分 -->
        <a href="#modal" class="modal-open-button">≡</a>
        <div class="modal common_item_box" id="modal">
            <ul class="modal-content">
                <li><a href="#" class="close">&times;</a></li>
                <li><a href="./page_attendance_management.php">トップ</a></li>
                <li><a href="./page_attendance_management_screen.php">勤怠管理画面</a></li>
                <?php if ($_SESSION['is_admin'] == 1) {
                    echo '<li><a href="./page_add_user.php">ユーザ追加</a></li>';
                }
                ?>
                <li><a href="./page_log_in.php">ログアウト</a></li>
            </ul>
        </div>

        <!-- JavaScriptをHTML内に直接記述 -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const modal = document.getElementById("modal");

                window.addEventListener("click", function(event) {
                    // モーダル外をクリックしたら閉じる
                    if (event.target !== modal) {
                        window.location.hash = ""; // モーダルを閉じる
                    }
                });
            });
        </script>
    <?php endif; ?>
</header>