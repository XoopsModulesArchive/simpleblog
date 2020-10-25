
$Id: read_me.txt,v 1.1 2006/03/20 16:18:58 mikhail Exp $

簡単なblog(ほぼ日記)です。
xoops-2.0.3/2.0.4/2.0.5で確認しています。
バージョン1系はまったく考慮されていません。

※このモジュールはキャッシュを有効にしないで下さい。
※はてな：ダイアリーっぽいとこから始まったので、一日に一つのことしか書けません。


■連絡先
	http://sourceforge.jp/projects/xoops-modules/
	
■インストール
	$xoops_home/modules配下にこのフォルダをコピーしてください。
	普通のモジュールと同じだと思います。

■機能
	・blogを作りたい人が申請できます
	・申請を許可するのはサイトの管理者です。
	・blog毎に以下の設定ができます。
		・ゲストツッコミ不可、登録ユーザツッコミ不可
　　		・ゲスト閲覧不可、登録ユーザツッコミ可
　　		・ゲスト閲覧可、登録ユーザツッコミ可
　　		・ゲスト、登録ユーザツッコミ可
	・一応、RSSを出力します(ゲスト閲覧可のblogのみ対象)。
	・リンク元を表示するようにしました。
	
■質問・うまく動作しない場合は
	公式(http://jp.xoops.org)か、sf.jp(http://sourceforge.jp/projects/xoops-modules/)に質問してください。
	その場合、OS・PHP・XOOPS・SimpleBlogのバージョンを明記してください。
	うまく動作しない場合(ブランクページになるなど)の場合は、
	　　管理画面 >> 一般設定 >> デバッグモード
	をPHP デバッグにしたときのログも一緒に出してください。

	
■アップグレード(順番にアップグレード方法を実行してください)
	0.1.2 以前からアップグレードする場合は、モジュールをアップデートしてから以下のSQL文を流してください。
		alter table <テーブルプレフィックス>simpleblog add last_update timestamp not null;
	
	0.1.2 からバージョンアップする場合は、以下のSQL文を流してください
		CREATE TABLE <テーブルプレフィックス>simpleblog_trackback (
			uid int(5) unsigned NOT NULL,
			t_date DATE not null,
			count int(8) unsigned,
			url text,
			KEY(uid, t_date)
		) ENGINE = ISAM;
		
		
	0.2-RC1 からバージョンアップする場合は、以下のSQL文を流してください。
		ALTER TABLE <テーブルプレフィックス>simpleblog_comment add create_date int(10) unsigned not null default 0;
		ALTER TABLE <テーブルプレフィックス>simpleblog_trackback add title varchar(250);
	0.2へのアップグレード
		データをバックアップした後、モジュール上書き、管理CPでモジュールのアップデートを行ってください。
	0.2.1RC1へのアップグレード
		データをバックアップした後、モジュールファイルを一度削除した上で、
		モジュールファイルをアップロード。
		管理CPでモジュールのアップデートを行ってください。
	0.2.1RC2へのアップグレード
		0.2.1RC1と同じことを行ってください。
	0.2.1RC2から0.2.1RC3へのアップグレード
		データをバックアップした後、モジュールファイルを一度削除した上で、
		モジュールファイルをアップロード。
		管理CPでモジュールのアップデートを行ってください。
