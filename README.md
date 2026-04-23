# tweetscan

Twitter (X) から特定のキーワードやトレンドを効率的に抽出・収集するスキャンツール

##  概要
SNS上の膨大な情報の中から、必要なデータのみを効率的に収集することを目的としたツールです。
特に、情報の鮮度が重要なトピック（経済動向、技術トレンドなど）において、ノイズを排除して意味のあるデータセットを構築するために開発しました。

##  特徴
- **特定キーワードの自動抽出:** 事前に設定したキーワードに基づき、関連するツイートを自動でスキャニング。
- **データクリーニング:** 重複や不要な情報を除外し、分析しやすい形式（JSON/CSVなど）で保存。
- **柔軟なカスタマイズ:** 検索条件や取得件数を容易に変更可能な設計。

##  使用技術
- Language: Python 3.x
- Libraries: Tweepy (Twitter API v2), Pandas, Dotenv
- Others: JSON / CSV (Data Storage)

##  セットアップ
1. リポジトリのクローン
   ```bash
   git clone [https://github.com/jiantailang/tweetscan.git](https://github.com/jiantailang/tweetscan.git)

   pip install -r requirements.txt

   API_KEY=your_api_key
API_SECRET=your_api_secret
ACCESS_TOKEN=your_access_token
ACCESS_TOKEN_SECRET=your_access_token_secret

python scanner.py

開発の背景
「文系とITの架け橋」を目指す中で、定性的なSNS上の議論を定量的なデータとして扱うスキルの必要性を感じ作成しました。
例えば、米国株のセンチメント分析や、特定の文化的トピックがどのように拡散されているかを客観的に把握するための第一歩として、このデータ収集ツールを位置づけています。

作者
jiantailang

Blog: chnlife.blog
