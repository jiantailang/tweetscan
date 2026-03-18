import feedparser
import os
import requests
from datetime import datetime, timedelta, timezone

# --- 設定項目 ---
# RSS.appなどで取得した就活亀さんのRSSフィードのURLに書き換えてください
RSS_URL = "https://rss.app/feeds/1IQu204TDxPM0wVW.xml"

# 抽出したいキーワード
KEYWORDS = ["インターン", "マイページ", "サマーインターン","選考","エントリー"]

# 結果を保存するファイル
OUTPUT_FILE = "results.md"
# ----------------

def send_discord(new_tweets):
    webhook_url = os.environ.get("DISCORD_WEBHOOK_URL")
    if not webhook_url:
        print("Discord Webhook URLが設定されていないため、通知はスキップします。")
        return

    content = "<@793470953375006761>\n🐢 **就活亀さん 新着抽出ツイート**\n\n"
    for tweet in new_tweets:
        content += tweet + "\n"
    
    # Discordの文字数制限(2000文字)対策として、少し余裕を持たせて切り詰める
    payload = {"content": content[:1990]}
    
    try:
        res = requests.post(webhook_url, json=payload)
        res.raise_for_status()
        print("Discordへ通知を送信しました！")
    except Exception as e:
        print(f"Discordへの送信に失敗しました: {e}")

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
        send_discord(new_tweets)
    else:
        print("新しく該当するツイートはありませんでした。")
        
        # --- 初回動作確認用（1回だけ実行） ---
        if "【テスト送信完了】" not in existing_content:
            print("初回の動作確認として、最新の該当ツイートを1件取得してDiscordに送信します。")
            for entry in feed.entries:
                text = f"{entry.get('title', '')} {entry.get('description', '')}"
                if any(keyword in text for keyword in KEYWORDS):
                    link = entry.link
                    test_tweet = f"- [動作テスト] {text[:60]}... \n  [👉 ツイートを見る]({link})\n"
                    send_discord(["※これはプログラムの初回動作確認メッセージです！\n" + test_tweet])
                    break

    # 今後テストが暴発しないように、初回実行時にフラグを書き込む
    if "【テスト送信完了】" not in existing_content:
        with open(OUTPUT_FILE, 'a', encoding="utf-8") as f:
            f.write("\n<!-- 【テスト送信完了】 -->\n")

if __name__ == "__main__":
    main()
