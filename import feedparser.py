import feedparser
import os
from datetime import datetime, timedelta, timezone

# --- 設定項目 ---
# RSS.appなどで取得した就活亀さんのRSSフィードのURLに書き換えてください
RSS_URL = "https://rss.app/feed/1IQu204TDxPM0wVW"

# 抽出したいキーワード
KEYWORDS = ["インターン", "マイページ", "サマーインターン"]

# 結果を保存するファイル
OUTPUT_FILE = "results.md"
# ----------------

def main():
    # JST（日本時間）の設定
    JST = timezone(timedelta(hours=+9), 'JST')
    now = datetime.now(JST)

    print(f"[{now.strftime('%Y-%m-%d %H:%M:%S')}] 実行開始...")
    
    feed = feedparser.parse(RSS_URL)
    if not feed.entries:
        print("ツイートが取得できませんでした。RSS URLを確認してください。")
        return

    # 既存のファイルを読み込んで、取得済みのリンクを把握する（重複保存防止）
    existing_content = ""
    if os.path.exists(OUTPUT_FILE):
        with open(OUTPUT_FILE, 'r', encoding='utf-8') as f:
            existing_content = f.read()

    new_tweets = []
    for entry in feed.entries:
        link = entry.link
        
        # 既に保存済みのツイートはスキップ
        if link in existing_content:
            continue

        # タイトルと説明文を結合してテキスト化
        text = f"{entry.get('title', '')} {entry.get('description', '')}"
        
        # キーワードが1つでも含まれているかチェック
        if any(keyword in text for keyword in KEYWORDS):
            published = entry.get('published', '日付不明')
            new_tweets.append(f"- [{published}] {text[:60]}... \n  [👉 ツイートを見る]({link})\n")

    # 新しい該当ツイートがあれば results.md に追記
    if new_tweets:
        mode = 'a' if os.path.exists(OUTPUT_FILE) else 'w'
        with open(OUTPUT_FILE, mode, encoding="utf-8") as f:
            if mode == 'w':
                f.write("# 🐢 就活亀さん 抽出ツイート一覧\n\n")
            f.write(f"## 📅 {now.strftime('%Y-%m-%d %H:%M:%S')} 取得分\n\n")
            for tweet in new_tweets:
                f.write(tweet + "\n")
        print(f"{len(new_tweets)}件の新しい該当ツイートを保存しました！")
    else:
        print("新しく該当するツイートはありませんでした。")

if __name__ == "__main__":
    main()
