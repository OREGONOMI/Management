<?php
require_once 'Base.php';

/**
 * User クラス
 * ユーザーの作成、取得、更新、削除を行うクラス
 */
class User extends Base
{
  protected $password;
  protected $family_name;
  protected $first_name;
  protected $is_admin;
  protected $is_deleted;

  /**
   * コンストラクタ
   */
  public function __construct()
  {
    // スーパークラスのコンストラクタを呼び出し
    parent::__construct();
  }

  /**
   * usersテーブルにユーザーを作成
   *
   * @param string $password パスワード
   * @param string $family_name 姓
   * @param string $first_name 名
   * @param int $is_admin 管理者フラグ（1: 管理者, 0: 一般ユーザー）
   * @return bool ユーザー作成の成功可否
   */
  public function createUser($password, $family_name, $first_name, $is_admin): bool
  {
    try {
      //管理者フラグが1で無ければ0にする
      if ($is_admin != 1) {
        //管理者フラグを非管理者に設定
        $is_admin = 0;
      }
      //削除フラグを未削除に設定
      $is_deleted = 0;

      // パスワードをハッシュ化
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      $sql = "INSERT INTO users (password, family_name, first_name, is_admin, is_deleted) 
      VALUES (:password, :family_name, :first_name, :is_admin, :is_deleted)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
      $stmt->bindValue(':family_name', $family_name, PDO::PARAM_STR);
      $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
      $stmt->bindValue(':is_admin', $is_admin, PDO::PARAM_INT);
      $stmt->bindValue(':is_deleted', $is_deleted, PDO::PARAM_INT);

      return $stmt->execute(); // 成功時 true, 失敗時 false
    } catch (PDOException $e) {
      $_SESSION['error'] = 'ユーザーの追加に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定された氏名とパスワードが一致するユーザーのIDを取得する
   *
   * @param string $password 入力された平文のパスワード
   * @param string $family_name ユーザーの姓
   * @param string $first_name ユーザーの名
   * @return int|null ユーザーID（見つからない場合はnull）
   */
  public function getUserIdIfExistsByPFF($password, $family_name, $first_name)
  {
    // ユーザーのパスワードハッシュを取得
    $sql = 'SELECT id, password FROM users WHERE family_name = :family_name AND first_name = :first_name';

    // SQL⽂を実⾏する準備
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':family_name', $family_name, PDO::PARAM_STR);
    $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
    $stmt->execute();

    // 結果を取得
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ユーザーが見つかり、パスワードが一致すればIDを返却
    if ($user && password_verify($password, $user['password'])) {
      return $user['id'];
    }

    return null; // 該当なし
  }


  /**
   * 指定したIDのユーザー情報を取得
   *
   * @param int $id ユーザーID
   * @return array ユーザー情報の配列
   */
  public function getUserById($id)
  {
    // SQLのSELECT文
    $sql = 'SELECT id,password,is_admin,is_deleted FROM users WHERE id = :id';
    // SQL文を実行する準備
    $stmt = $this->pdo->prepare($sql);
    // 変数の値をバインド
    $stmt->bindValue(':id', intval($id), PDO::PARAM_INT);
    // SQL文を実行
    $stmt->execute();
    // 結果を1件だけ取得
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * 指定したIDと権限に合わせてユーザー情報を取得
   *
   * @param int $id ユーザーID
   * @param int $is_admin 検索者側のID
   * @return array ユーザー情報の配列
   */
  public function getUserById_and_admin($id, $is_admin)
  {
    if ($is_admin == 1) {
      // SQLのSELECT文
      $sql = 'SELECT id,password,family_name,first_name,is_admin,is_deleted FROM users WHERE is_deleted = 0 ORDER BY CASE WHEN id = :id THEN 0 ELSE 1 END, id ASC';
      // SQL文を実行する準備
      $stmt = $this->pdo->prepare($sql);
      // 変数の値をバインド
      $stmt->bindValue(':id', intval($id), PDO::PARAM_INT);
      // SQL文を実行
      $stmt->execute();
      // 結果を1件だけ取得
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      // SQLのSELECT文
      $sql = 'SELECT id,password,family_name,first_name,is_admin,is_deleted FROM users WHERE id = :id';
      // SQL文を実行する準備
      $stmt = $this->pdo->prepare($sql);
      // 変数の値をバインド
      $stmt->bindValue(':id', intval($id), PDO::PARAM_INT);
      // SQL文を実行
      $stmt->execute();
      // 結果を1件だけ取得
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  /**
   * 指定したIDのユーザー情報を更新
   *
   * @param int $id ユーザーID
   * @param string $password パスワード（ハッシュ化前）
   * @param string $family_name 姓
   * @param string $first_name 名
   * @param int $is_admin 管理者フラグ（1: 管理者, 0: 一般ユーザー）
   * @return bool 更新の成功可否
   */
  public function updateUser($id, $password, $family_name, $first_name, $is_admin): bool
  {
    try {
      // ここに処理を追加（例: データベースの更新や他のアクション）
      // SQLの更新です
      $sql = '';
      $sql .= 'update users set ';
      $sql .= 'password = :password, ';
      $sql .= 'family_name = :family_name,';
      $sql .= 'first_name = :first_name ';
      $sql .= 'is_admin = :is_admin ';
      $sql .= 'where id = :id ';

      // SQL⽂を実⾏する準備をします。
      $stmt = $this->pdo->prepare($sql);
      //ユーザーIDを取得
      $stmt->bindValue(':password', $password, PDO::PARAM_STR);
      $stmt->bindValue(':family_name', $family_name, PDO::PARAM_STR);
      $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
      $stmt->bindValue(':is_admin', $is_admin, PDO::PARAM_INT);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      // SQL⽂を実⾏します。
      // insert⽂の実⾏結果を受け取る必要はありません。
      // SQL⽂を実⾏したときにエラーが起きたら例外がスローされるためです。
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = 'ユーザー情報の更新に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定したIDのユーザーを削除（論理削除）
   *
   * @param int $id ユーザーID
   * @return bool 削除の成功可否
   */
  public function deleteUser($id): bool
  {
    try {
      // ここに処理を追加（例: データベースの更新や他のアクション）
      // SQLの更新です
      $sql = '';
      $sql .= 'update users set ';
      $sql .= 'is_deleted = 1 ';
      $sql .= 'where id = :id ';

      // SQL⽂を実⾏する準備をします。
      $stmt = $this->pdo->prepare($sql);
      //IDの状態入力
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      // SQL⽂を実⾏します。
      // insert⽂の実⾏結果を受け取る必要はありません。
      // SQL⽂を実⾏したときにエラーが起きたら例外がスローされるためです。
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = 'ユーザーの削除に失敗しました。' . $e->getMessage();
      return false;
    }
  }
}
