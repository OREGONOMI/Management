<?php
require_once 'Base.php';
/**
 * 勤怠データをCSVとしてエクスポートするクラス
 */
class CSVExporter extends Base
{
  /**
   * コンストラクタ
   * データベース接続を初期化
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * 勤怠データをCSV形式で出力
   *
   * @param int $user_id ユーザーID
   * @param string $work_date 勤務日（YYYY-MM形式）
   * @return bool 成功時 true, 失敗時 false
   */
  public function exportToCSV($user_id, $work_date): bool
  {
    try {
      // 指定された年月の範囲を取得
      $start_date = date('Y-m-01', strtotime($work_date));
      $end_date = date('Y-m-t', strtotime($work_date));
      $total_days = date('t', strtotime($work_date)); // 指定月の日数

      // データ取得クエリ
      $sql = "SELECT work_date, start_time, break_time, end_time, remarks 
                FROM attendance 
                WHERE user_id = :user_id 
                AND work_date BETWEEN :start_date AND :end_date 
                ORDER BY work_date";

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
      $stmt->execute();

      $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // 勤怠データを日付ごとに整理
      $attendance_by_date = [];
      foreach ($attendances as $attendance) {
        $day = date('d', strtotime($attendance['work_date']));
        $attendance_by_date[$day] = $attendance;
      }

      // CSV出力処理
      $filename = "attendance_{$user_id}_{$work_date}.csv";
      header('Content-Type: text/csv; charset=UTF-8');
      header('Content-Disposition: attachment; filename="' . $filename . '"');

      $output = fopen('php://output', 'w');
      fputcsv($output, ['勤務日', '始業時間', '休憩時間', '終了時間', '合計時間', '備考']);

      // 1日〜月末までループしてデータを出力
      for ($day = 1; $day <= $total_days; $day++) {
        $day_str = sprintf('%02d', $day); // 2桁表記
        if (isset($attendance_by_date[$day_str])) {
          $row = $attendance_by_date[$day_str];
          $start_time = !empty($row['start_time']) ? $row['start_time'] : '未入力';
          $end_time = !empty($row['end_time']) ? $row['end_time'] : '未入力';
          $break_time = !empty($row['break_time']) ? $row['break_time'] : '00:00';
          $remarks = !empty($row['remarks']) ? $row['remarks'] : '';

          // 合計時間の計算
          if ($start_time !== '未入力' && $end_time !== '未入力') {
            $start = strtotime($row['start_time']);
            $end = strtotime($row['end_time']);
            list($h, $i) = explode(':', $break_time);
            $break_seconds = ($h * 3600) + ($i * 60);
            $total_seconds = max(0, ($end - $start) - $break_seconds);
            $total_time = sprintf('%02d:%02d', floor($total_seconds / 3600), floor(($total_seconds % 3600) / 60));
          } else {
            $total_time = '未入力';
          }
        } else {
          // データがない場合
          $start_time = '未入力';
          $end_time = '未入力';
          $break_time = '00:00';
          $total_time = '未入力';
          $remarks = '';
        }

        // fputcsv($output, ["{$work_date}-{$day_str}", $start_time, $break_time, $end_time, $total_time, $remarks]);
        fputcsv($output, [sprintf('%04d-%02d-%02d', substr($work_date, 0, 4), substr($work_date, 5, 2), $day_str), $start_time, $break_time, $end_time, $total_time, $remarks]);

      }

      fclose($output);
      return true;
    } catch (PDOException $e) {
      $_SESSION['error'] = 'CSV形式の出力に失敗しました。' . $e->getMessage();
      return false;
    }
  }
}
