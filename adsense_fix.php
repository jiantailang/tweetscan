<?php
/**
 * AdSense審査対策 自動修正スクリプト
 * 使い方:
 *   1. このファイルをpublic_htmlにアップロード
 *   2. ブラウザで https://chnlife.blog/adsense_fix.php?key=chnlife2026 にアクセス
 *   3. 完了後、このファイルを削除
 */

define('SECRET_KEY', 'chnlife2026');

if (!isset($_GET['key']) || $_GET['key'] !== SECRET_KEY) {
    die('Unauthorized');
}

// WordPress読み込み
$wp_load = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('wp-load.php not found');
}
require_once $wp_load;

$results = [];

// ============================================================
// 1. プライバシーポリシーページ作成
// ============================================================
$privacy_exists = get_page_by_path('privacy-policy');
if (!$privacy_exists) {
    $privacy_content = '<!-- wp:heading -->
<h2 class="wp-block-heading">広告について</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当サイト（chnlife.blog）はGoogle AdSenseを利用した広告を掲載しています。Googleはcookieを使用して、ユーザーのサイトへの過去のアクセス情報に基づいて広告を配信します。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>ユーザーは<a href="https://www.google.com/settings/ads">Googleの広告設定ページ</a>にてパーソナライズ広告を無効にすることができます。また、<a href="http://www.aboutads.info/choices/">www.aboutads.info</a> にアクセスすることで、パーソナライズ広告に使われる第三者配信事業者のcookieを無効にすることができます。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">アクセス解析ツールについて</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当サイトはGoogleによるアクセス解析ツール「Googleアナリティクス」を使用しています。このGoogleアナリティクスはデータの収集のためにcookieを使用しています。このデータは匿名で収集されており、個人を特定するものではありません。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>この機能はcookieを無効にすることで収集を拒否することができます。お使いのブラウザの設定をご確認ください。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">免責事項</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当サイトのコンテンツ・情報について、できる限り正確な情報を提供するよう努めておりますが、正確性や安全性を保証するものではありません。当サイトの情報をもとに行われた行動により損害等が生じた場合、当サイトは一切の責任を負いかねます。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>また、当サイトからリンクやバナーなどによって他のサイトに移動された場合、移動先サイトで提供される情報、サービス等について一切の責任を負いません。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">著作権について</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当サイトで掲載している文章・画像などの著作権は、運営者に帰属します。無断転載・複製はご遠慮ください。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">運営者情報</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>運営者：Kentaro<br>サイト名：こちら北京チャイナライフ<br>URL：https://chnlife.blog/<br>お問い合わせ：当サイトのお問い合わせページよりご連絡ください。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">プライバシーポリシーの変更について</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>当サイトは、個人情報に関して適用される日本の法令を遵守するとともに、本プライバシーポリシーの内容を適宜見直しその改善に努めます。修正された最新のプライバシーポリシーは常に本ページにて開示されます。</p>
<!-- /wp:paragraph -->';

    $privacy_id = wp_insert_post([
        'post_title'   => 'プライバシーポリシー',
        'post_name'    => 'privacy-policy',
        'post_content' => $privacy_content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
    $results[] = is_wp_error($privacy_id)
        ? '❌ プライバシーポリシー作成失敗: ' . $privacy_id->get_error_message()
        : '✅ プライバシーポリシーページ作成完了 (ID: ' . $privacy_id . ')';
} else {
    $results[] = 'ℹ️ プライバシーポリシーページは既に存在します (ID: ' . $privacy_exists->ID . ')';
    $privacy_id = $privacy_exists->ID;
}

// ============================================================
// 2. お問い合わせページ作成
// ============================================================
$contact_exists = get_page_by_path('contact');
if (!$contact_exists) {
    // CF7のフォームIDを取得
    $cf7_forms = get_posts(['post_type' => 'wpcf7_contact_form', 'posts_per_page' => 1]);
    $cf7_shortcode = '';
    if (!empty($cf7_forms)) {
        $cf7_shortcode = '[contact-form-7 id="' . $cf7_forms[0]->ID . '" title="' . $cf7_forms[0]->post_title . '"]';
    }

    $contact_content = '<!-- wp:paragraph -->
<p>当サイトへのご質問・ご意見・取材のご依頼などは、下記フォームよりお気軽にお問い合わせください。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>※ スパム対策のため、ご返信まで数日かかる場合があります。</p>
<!-- /wp:paragraph -->

' . ($cf7_shortcode ? "<!-- wp:shortcode -->\n{$cf7_shortcode}\n<!-- /wp:shortcode -->" : '<!-- wp:paragraph --><p>お問い合わせフォームは準備中です。</p><!-- /wp:paragraph -->');

    $contact_id = wp_insert_post([
        'post_title'   => 'お問い合わせ',
        'post_name'    => 'contact',
        'post_content' => $contact_content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
    $results[] = is_wp_error($contact_id)
        ? '❌ お問い合わせページ作成失敗: ' . $contact_id->get_error_message()
        : '✅ お問い合わせページ作成完了 (ID: ' . $contact_id . ')';
} else {
    $results[] = 'ℹ️ お問い合わせページは既に存在します (ID: ' . $contact_exists->ID . ')';
    $contact_id = $contact_exists->ID;
}

// ============================================================
// 3. 運営者情報（Aboutページ）作成
// ============================================================
$about_exists = get_page_by_path('about');
if (!$about_exists) {
    $about_content = '<!-- wp:heading -->
<h2 class="wp-block-heading">このブログについて</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>「こちら北京チャイナライフ」は、中国・北京在住の日本人Kentaroが運営する中国生活・旅行情報ブログです。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>中国に実際に住んでみて感じたリアルな生活情報、観光スポット、グルメ、アプリの使い方など、日本ではなかなか手に入らない生きた情報をお届けします。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">運営者プロフィール</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>名前：</strong>Kentaro<br><strong>居住地：</strong>中国・北京<br><strong>ブログ開始：</strong>2024年<br><strong>発信テーマ：</strong>中国旅行・在住生活・文化・テクノロジー</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>中国は日本から近いのに、まだまだ知られていない魅力がたくさんあります。このブログが、中国を旅する方・中国に興味を持つ方の役に立てれば幸いです。</p>
<!-- /wp:paragraph -->';

    $about_id = wp_insert_post([
        'post_title'   => '運営者情報',
        'post_name'    => 'about',
        'post_content' => $about_content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
    $results[] = is_wp_error($about_id)
        ? '❌ 運営者情報ページ作成失敗: ' . $about_id->get_error_message()
        : '✅ 運営者情報ページ作成完了 (ID: ' . $about_id . ')';
} else {
    $results[] = 'ℹ️ 運営者情報ページは既に存在します';
    $about_id = $about_exists->ID;
}

// ============================================================
// 4. フッターメニューにページを追加
// ============================================================
$footer_menu_name = 'フッターメニュー';
$footer_menu = wp_get_nav_menu_object($footer_menu_name);

if (!$footer_menu) {
    $menu_id = wp_create_nav_menu($footer_menu_name);
    $results[] = '✅ フッターメニュー作成完了';
} else {
    $menu_id = $footer_menu->term_id;
    $results[] = 'ℹ️ フッターメニューは既に存在します';
}

if (!is_wp_error($menu_id)) {
    // 既存アイテムを確認して重複追加しない
    $existing_items = wp_get_nav_menu_items($menu_id);
    $existing_object_ids = [];
    if ($existing_items) {
        foreach ($existing_items as $item) {
            $existing_object_ids[] = $item->object_id;
        }
    }

    $pages_to_add = [
        isset($privacy_id) ? $privacy_id : null,
        isset($contact_id) ? $contact_id : null,
        isset($about_id) ? $about_id : null,
    ];

    foreach ($pages_to_add as $page_id) {
        if ($page_id && !in_array($page_id, $existing_object_ids)) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-object-id' => $page_id,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }

    // フッターの場所にメニューを割り当て
    $locations = get_theme_mod('nav_menu_locations');
    $theme_locations = get_registered_nav_menus();
    foreach ($theme_locations as $location => $description) {
        if (stripos($location, 'footer') !== false || stripos($description, 'フッター') !== false || stripos($description, 'footer') !== false) {
            $locations[$location] = $menu_id;
        }
    }
    set_theme_mod('nav_menu_locations', $locations);
    $results[] = '✅ フッターメニューにページを追加完了';
}

// ============================================================
// 5. 全投稿のnoindex確認・修正（All in One SEO）
// ============================================================
$posts = get_posts([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
]);

$noindex_fixed = 0;
foreach ($posts as $post) {
    // AIOSEOのnoindex設定を確認
    $aioseo_noindex = get_post_meta($post->ID, '_aioseo_noindex', true);
    $robots_noindex = get_post_meta($post->ID, '_robots_noindex', true);

    if ($aioseo_noindex == '1' || $aioseo_noindex === true) {
        update_post_meta($post->ID, '_aioseo_noindex', '0');
        $noindex_fixed++;
    }
    if ($robots_noindex == '1' || $robots_noindex === true) {
        update_post_meta($post->ID, '_robots_noindex', '0');
        $noindex_fixed++;
    }
}
$results[] = $noindex_fixed > 0
    ? '✅ noindex設定を修正した投稿: ' . $noindex_fixed . '件'
    : 'ℹ️ noindex設定の問題は見つかりませんでした';

// ============================================================
// 6. 現在の全投稿一覧と文字数チェック
// ============================================================
$results[] = "\n📋 全投稿チェック:";
foreach ($posts as $post) {
    $content = strip_tags($post->post_content);
    $char_count = mb_strlen($content, 'UTF-8');
    $status = $char_count < 800 ? '⚠️ 文字数少' : '✅';
    $results[] = "  {$status} [{$char_count}文字] {$post->post_title}";
}

// ============================================================
// 7. 完了メッセージ出力
// ============================================================
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>AdSense修正完了</title>
<style>
body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
.card { background: white; padding: 20px; border-radius: 8px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1 { color: #2c3e50; }
pre { background: #ecf0f1; padding: 15px; border-radius: 4px; white-space: pre-wrap; line-height: 1.8; }
.warning { color: #e74c3c; background: #fdecea; padding: 15px; border-radius: 4px; margin-top: 20px; }
a { color: #3498db; }
</style>
</head>
<body>
<div class="card">
  <h1>🎉 AdSense審査対策 自動修正完了</h1>
  <pre><?php echo implode("\n", $results); ?></pre>
</div>

<div class="card">
  <h2>✅ 作成されたページ</h2>
  <ul>
    <li><a href="<?php echo home_url('/privacy-policy/'); ?>" target="_blank">プライバシーポリシー</a></li>
    <li><a href="<?php echo home_url('/contact/'); ?>" target="_blank">お問い合わせ</a></li>
    <li><a href="<?php echo home_url('/about/'); ?>" target="_blank">運営者情報</a></li>
  </ul>
</div>

<div class="card">
  <h2>📝 次にやること（手動）</h2>
  <ol>
    <li>⚠️ 文字数が少ない記事（上記リストで「文字数少」と表示された記事）をリライトする</li>
    <li>All in One SEO → サイトマップ設定を確認・再送信する</li>
    <li>Google Search Console でサイトマップを再送信する</li>
    <li>2026年6月16日以降にAdSense再審査リクエストを送る</li>
  </ol>
</div>

<div class="warning">
  <strong>⚠️ 重要：作業完了後、このファイル（adsense_fix.php）を必ず削除してください！</strong><br>
  Xserverファイルマネージャー → public_html → adsense_fix.php → 削除
</div>
</body>
</html>
