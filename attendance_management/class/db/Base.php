<?php
date_default_timezone_set('Asia/Tokyo'); 
session_start(); // セッション開始

/**
 * データベース接続を管理する基底クラス
 */
class Base
{
  // 定数の定義
  /** @var string データベース名 */
  const DATABASE_NAME = 'attendance_management';

  /** @var string データベースホスト */
  const DATABASE_HOST = 'localhost';

  /** @var string データベースユーザー名 */
  const DATABASE_USER = 'root';

  /** @var string データベースパスワード */
  const DATABASE_PASSWORD = '';

  /** @var PDO PDOインスタンス */
  protected $pdo;

  /**
   * コンストラクタ
   * 
   * データベースに接続し、PDOインスタンスを初期化する。
   * 接続エラーが発生した場合は、エラーメッセージをセッションに保存し、エラーページへリダイレクトする。
   * 
   * @throws PDOException データベース接続に失敗した場合
   */
  public function __construct()
  {
    try {
      // データベースに接続
      $dsn = 'mysql:dbname=' . self::DATABASE_NAME . ';host=' . self::DATABASE_HOST . ';charset=utf8';
      $this->pdo = new PDO($dsn, self::DATABASE_USER, self::DATABASE_PASSWORD);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return true;
    } catch (PDOException $e) {
      // エラーメッセージをセッションに格納
      $_SESSION['error'] = 'データベース接続に失敗しました。' . $e->getMessage();
      // ログインページにリダイレクト
      header('Location: error.php');
      return false;
      // exit();
    }
  }
}
