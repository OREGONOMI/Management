<?php
require_once 'Base.php';

/**
 * 勤怠管理クラス
 */
class Attendance extends Base
{
  protected $id; //AUTO_INCREMENTのため不要？
  protected $user_id;
  protected $work_date;
  protected $start_time;
  protected $break_time;
  protected $remarks;

  //コンストラクタ
  public function __construct()
  {
    // スーパークラスのコンストラクタを呼び出し
    parent::__construct();
  }

  /**
   * 始業時間を記録する（更新または新規作成）
   *
   * @param int $user_id ユーザーID
   * @param string $work_date 勤務日 (YYYY-MM-DD)
   * @param string $start_time 始業時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function upcreateStartTime($user_id, $work_date, $start_time): bool
  {
    try {
      // まず、指定日のレコードがあるか確認
      $sql = "SELECT COUNT(*) FROM attendance WHERE user_id = :user_id AND work_date = :work_date";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->fetchColumn();

      if ($count > 0) {
        // レコードが存在する場合は更新
        $sql = "UPDATE attendance SET start_time = :start_time WHERE user_id = :user_id AND work_date = :work_date";
      } else {
        // レコードが存在しない場合は新規作成
        $sql = "INSERT INTO attendance (user_id, work_date, start_time, break_time, end_time, remarks) 
                    VALUES (:user_id, :work_date, :start_time, NULL, NULL, NULL)";
      }

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '始業時間の記録に失敗しました。' . $e->getMessage();
      return false;
    }
  }


  /**
   * 休憩時間の期間を記録する
   *
   * @param int $user_id ユーザーID
   * @param string $work_date 勤務日 (YYYY-MM-DD)
   * @param string $break_time 休憩時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function recordBreakTime($user_id, $work_date, $break_time): bool
  {
    try {
      $sql = "UPDATE attendance SET break_time = :break_time WHERE user_id = :user_id AND work_date = :work_date";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      $stmt->bindValue(':break_time', $break_time, PDO::PARAM_STR);

      return $stmt->execute(); // 成功時 true, 失敗時 false
    } catch (PDOException $e) {
      $_SESSION['error'] = '休憩時間の記録に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 終業時間を記録する（更新または新規作成）
   *
   * @param int $user_id ユーザーID
   * @param string $work_date 勤務日 (YYYY-MM-DD)
   * @param string $end_time 終業時間 (HH:MM:SS)
   * @return bool 成功時 true, 失敗時 false
   */
  public function upcreateEndTime($user_id, $work_date, $end_time): bool
  {
    try {
      // まず、指定日のレコードがあるか確認
      $sql = "SELECT COUNT(*) FROM attendance WHERE user_id = :user_id AND work_date = :work_date";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->fetchColumn();

      if ($count > 0) {
        // レコードが存在する場合は更新
        $sql = "UPDATE attendance SET end_time = :end_time WHERE user_id = :user_id AND work_date = :work_date";
      } else {
        // レコードが存在しない場合は新規作成
        $sql = "INSERT INTO attendance (user_id, work_date, start_time, break_time, end_time, remarks) 
                    VALUES (:user_id, :work_date, NULL, NULL, :end_time, NULL)";
      }

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '終業時間の記録に失敗しました。' . $e->getMessage();
      return false;
    }
  }


  /**
   * 指定したユーザーと日付の備考を更新または新規作成する。
   *
   * @param int    $user_id  ユーザーID
   * @param string $date     勤務日 (YYYY-MM-DD形式)
   * @param string $remarks  備考の内容
   * @return bool  成功時にtrue、失敗時にfalse
   */
  public function upcreateRemarks($user_id, $date, $remarks)
  {
    try {
      // まず、指定日のレコードがあるか確認
      $sql = "SELECT COUNT(*) FROM attendance WHERE user_id = :user_id AND work_date = :work_date";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $date, PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->fetchColumn();

      if ($count > 0) {
        // レコードが存在する場合は更新
        $sql = "UPDATE attendance SET remarks = :remarks WHERE user_id = :user_id AND work_date = :work_date";
      } else {
        // レコードが存在しない場合は新規作成
        $sql = "INSERT INTO attendance (user_id, work_date, start_time, break_time, end_time, remarks) VALUES (:user_id, :work_date, NULL, NULL, NULL, :remarks)";
      }

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':remarks', $remarks, PDO::PARAM_STR);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $date, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '備考欄の追加に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定されたユーザIDで終業時刻がないレコードを取得する
   *
   * @param string $user_id ユーザーID
   * @return array 終業時刻がNULLのレコードの配列
   */
  public function getDayWithoutEndTimeByUW($user_id)
  {
    $sql = 'SELECT * FROM attendance WHERE user_id = :user_id AND end_time IS NULL';

    // SQL⽂を実⾏する準備
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();

    // 結果を連想配列で取得
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * 指定したユーザーの当日の勤怠記録を取得する
   *
   * @param int $user_id ユーザーID
   * @param date $work_date 年月日
   * @param int $mode $mode=0:work_dateの年月日までを見て1日分を取得/$mode=1:work_dateの年月までを見て1か月分を取得
   * @return array 勤怠記録の連想配列（データがない場合は空配列）
   */
  public function getAttendanceByUser($user_id, $work_date, $mode)
  {
    if ($mode == 0) {
      try {
        //1日分を取得
        // SQLの準備
        $sql = 'SELECT * FROM attendance WHERE user_id = :user_id AND work_date = :work_date';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);

        // SQLを実行
        $stmt->execute();

        // 結果を連想配列で取得
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        $_SESSION['error'] = '勤怠記録の取得に失敗しました。' . $e->getMessage();
        return false;
      }
    } else if ($mode == 1) {
      try {
        // ymd（YYYY-MM-DD）から年と月を取得
        //赤波線出るが方はあっているためこのままでよし
        $year = date('Y', strtotime($work_date));
        $month = date('m', strtotime($work_date));
        $work_date = sprintf('%04d-%02d', $year, $month) . '%'; // 例: "2025-03%"

        // 1ヶ月分の勤怠データを取得
        $sql = 'SELECT * FROM attendance
        WHERE user_id = :user_id
        AND work_date LIKE :work_date
        ORDER BY work_date ASC';

        // SQLを準備
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);

        // $stmt->bindValue(':work_date', $work_date . '%', PDO::PARAM_STR); // "YYYY-MM%" に変換

        // SQLを実行
        $stmt->execute();

        // 結果を連想配列で取得
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        $_SESSION['error'] = '勤怠記録の取得に失敗しました。' . $e->getMessage();
        return false;
      }
    }
  }

  /**
   * 指定したユーザーの当日の勤怠記録を取得する
   *
   * @param int $id ID
   * @return array 勤怠記録の連想配列（データがない場合は空配列）
   */
  public function getAttendanceByID($id)
  {
    try {
      //1日分を取得
      // SQLの準備
      $sql = 'SELECT * FROM attendance WHERE id = :id';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);

      // SQLを実行
      $stmt->execute();

      // 結果を連想配列で取得
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $_SESSION['error'] = '勤怠記録の取得に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定したIDの勤怠情報を更新
   *
   * @param int $id 勤怠ID
   * @param int $user_id ユーザーID
   * @param string $work_date 勤務日
   * @param string $start_time 始業時間
   * @param string $break_time 休憩時間
   * @param string $end_time 終業時間
   * @param string|null $remarks 備考
   * @return bool 成功時 true, 失敗時 false
   */
  public function updateAttendance($id, $user_id, $work_date, $start_time, $break_time, $end_time, $remarks): bool
  {
    try {
      // ここに処理を追加（例: データベースの更新や他のアクション）
      // SQLの更新です
      $sql = '';
      $sql .= 'update attendance set ';
      $sql .= 'work_date = :work_date, ';
      $sql .= 'start_time = :start_time,';
      $sql .= 'break_time = :break_time, ';
      $sql .= 'end_time = :end_time, ';
      $sql .= 'remarks = :remarks ';
      $sql .= 'where id = :id AND user_id = :user_id';

      // SQL⽂を実⾏する準備をします。
      $stmt = $this->pdo->prepare($sql);
      //勤怠IDを取得
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':work_date', $work_date, PDO::PARAM_STR);
      $stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
      $stmt->bindValue(':break_time', $break_time, PDO::PARAM_STR);
      $stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
      $stmt->bindValue(':remarks', $remarks, PDO::PARAM_STR);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      // SQL⽂を実⾏します。
      // insert⽂の実⾏結果を受け取る必要はありません。
      // SQLを実行
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '勤怠情報の更新に失敗しました。' . $e->getMessage();
      return false;
    }
  }

  /**
   * 指定したIDの勤怠記録を削除
   *
   * @param int $id 勤怠ID
   * @return bool 成功時 true, 失敗時 false
   */
  public function deleteAttendance($id): bool
  {
    try {
      // ここに処理を追加（例: データベースの更新や他のアクション）
      // SQLの更新です
      $sql = '';
      $sql .= 'DELETE FROM attendance WHERE id = :id';

      // SQL⽂を実⾏する準備をします。
      $stmt = $this->pdo->prepare($sql);
      //IDの状態入力
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      // SQLを実行
      return $stmt->execute();
    } catch (PDOException $e) {
      $_SESSION['error'] = '勤怠記録削除に失敗しました。' . $e->getMessage();
      return false;
    }
  }
}
