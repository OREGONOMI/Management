<?php
require_once 'Base.php';

/**
 * 休憩時間を管理するクラス
 */
class Breaks extends Base
{
  /** @var int 勤怠ID */
  protected $attendance_id;

  /** @var string 休憩開始時間 */
  protected $break_start_time;

  /** @var string 休憩終了時間 */
  protected $break_end_time;

  /**
   * コンストラクタ
   * データベース接続を初期化
   */
  public function __construct()
  {
    // スーパークラスのコンストラクタを呼び出し
    parent::__construct();
  }

  /**
   * 休憩時間を追加
   *
   * @param int $attendance_id 勤怠ID
   * @param string $break_start_time 休憩開始時間
   * @param string $break_end_time 休憩終了時間
   * @return bool 成功時 true, 失敗時 false
   */
  public function addBreak($attendance_id, $break_start_time, $break_end_time): bool
  {
    try {
      $sql = "INSERT INTO attendance (attendance_id, break_start_time, break_end_time) 
      VALUES (:attendance_id, :break_start_time, :break_end_time)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);
      $stmt->bindValue(':break_start_time', $break_start_time, PDO::PARAM_STR);
      $stmt->bindValue(':break_end_time', $break_end_time, PDO::PARAM_STR);

      return $stmt->execute(); // 成功時 true, 失敗時 false
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩時間の追加に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 休憩時間を登録する
   *
   * @param int $attendance_id 勤怠ID
   * @param string $break_start_time 休憩開始時間 (HH:MM:SS)
   * @param string $break_end_time 休憩終了時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function createBreakTime($attendance_id, $break_start_time, $break_end_time): bool
  {
    try {
      $sql = "INSERT INTO breaks (attendance_id, break_start_time, break_end_time) 
                VALUES (:attendance_id, :break_start_time, :break_end_time)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);
      $stmt->bindValue(':break_start_time', $break_start_time, PDO::PARAM_STR);
      $stmt->bindValue(':break_end_time', $break_end_time, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩時間の登録に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 休憩開始時間を記録する（更新または新規作成）
   *
   * @param int $attendance_id 勤怠ID
   * @param string $break_start_time 休憩時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function createBreakStartTime($attendance_id, $break_start_time): bool
  {
    try {
      $sql = "INSERT INTO breaks (attendance_id,break_start_time) VALUES (:attendance_id,:break_start_time)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);
      $stmt->bindValue(':break_start_time', $break_start_time, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩時間の記録に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 休憩終了時間を記録する（更新または新規作成）
   *
   * @param int $attendance_id 勤怠ID
   * @param string $break_end_time 休憩終了時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function updateBreakEndTime($attendance_id, $break_end_time): bool
  {
    try {
      $sql = "UPDATE breaks 
                SET break_end_time = :break_end_time 
                WHERE attendance_id = :attendance_id 
                AND break_end_time IS NULL 
                LIMIT 1"; // NULL の行が複数あっても、1つだけ更新

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);
      $stmt->bindValue(':break_end_time', $break_end_time, PDO::PARAM_STR);

      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩終了時間の更新に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定した勤怠IDの休憩情報を取得(休憩総時間の計算時に使用する)
   *
   * @param int $attendance_id 勤怠ID
   * @return array|false 休憩情報の配列、または失敗時 false
   */
  public function getBreaksByAttendance($attendance_id)
  {
    try {
      $sql = 'SELECT * FROM breaks WHERE attendance_id = :attendance_id';

      // SQL文の準備
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);

      // SQLを実行
      $stmt->execute();

      // 結果を連想配列で取得
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩情報の取得に失敗しました。' . $e->getMessage();
      return false;
    } catch (Exception $e) {
      $_SESSION['error'] = '入力エラー: ' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定したIDの休憩時間を削除
   *
   * @param int $attendance_id 勤怠管理ID
   * @return bool 成功時 true, 失敗時 false
   */
  public function deleteBreak($attendance_id): bool
  {
    try {
      // ここに処理を追加（例: データベースの更新や他のアクション）
      // SQLの更新です
      $sql = '';
      $sql .= 'DELETE FROM breaks WHERE attendance_id = :attendance_id';

      // SQL⽂を実⾏する準備をします。
      $stmt = $this->pdo->prepare($sql);
      //IDの状態入力
      $stmt->bindValue(':attendance_id', $attendance_id, PDO::PARAM_INT);
      // SQLを実行
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩時間の削除に失敗しました。' . $e->getMessage();
      return false;
    }
  }
}
