<?php

/**
 * Koko Analytics Pro upsell banner.
 *
 * Drop this file in your plugin (e.g. views/koko-pro-upsell.php) and render it
 * from your dashboard output with:
 *
 *     $koko_pro_visitors = 622; // total visitors for the selected period
 *     include __DIR__ . '/views/koko-pro-upsell.php';
 *
 * No JavaScript, no external assets. Flag glyphs are plain emoji.
 * All class names are prefixed with `koko-pro-` to avoid clashing with wp-admin.
 */

if (! defined('ABSPATH')) {
    exit;
}

// Fallbacks so the file also renders standalone.
$koko_pro_visitors  = isset($totals) && is_object($totals) ? (int) $totals->visitors : 0;
$koko_pro_price_url = 'https://www.kokoanalytics.com/pricing/?utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-dashboard-upgrade';
$koko_pro_more_url  = 'https://www.kokoanalytics.com/features/?utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-dashboard-upgrade#pro-features';
?>
<style>
.koko-pro-wrap *{box-sizing:border-box}
.koko-pro-wrap{container-type:inline-size;container-name:kokopro}
.koko-pro-banner{position:relative;display:grid;grid-template-columns:1.35fr 0.95fr 320px;gap:34px;align-items:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:#1d2939;background:white;border-radius:.375rem;padding:1rem;margin:0 0 1.5rem;overflow:hidden}
.koko-pro-brandrow{display:flex;align-items:center;gap:9px}
.koko-pro-logo{width:26px;height:26px;flex:none;display:block;filter:drop-shadow(0 1px 1px rgba(140,2,4,.18))}
.koko-pro-logo svg{width:100%;height:100%;display:block}
.koko-pro-badge{display:inline-flex;align-items:baseline;gap:6px;color:#8a7778;font-size:11.5px;font-weight:700;letter-spacing:.13em;text-transform:uppercase}
.koko-pro-badge b{color:#b60205;font-weight:700}
.koko-pro-h{font-size:23px;line-height:1.22;font-weight:700;letter-spacing:-.01em;margin:14px 0 8px;color:#2a1416}
.koko-pro-h em{font-style:normal;color:inherit;text-decoration:none}
.koko-pro-sub{font-size:14px;line-height:1.55;color:#5a4b4c;max-width:44ch;margin:0}
.koko-pro-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
.koko-pro-chip{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#4a3436;background:#fff;border:1px solid #ecd9d9;padding:6px 11px 6px 9px;border-radius:20px}
.koko-pro-chip svg{flex:none}
.koko-pro-peek{position:relative;background:#fff;border:1px solid #eddcda;border-radius:9px;padding:13px 15px 15px;box-shadow:0 1px 2px rgba(140,2,4,.05)}
.koko-pro-peek-h{display:flex;align-items:center;justify-content:space-between;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a08d8d;margin-bottom:10px}
.koko-pro-lock{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;letter-spacing:.04em;color:#6a747e;background:#eef1f4;border:1px solid #dde3e9;padding:3px 8px;border-radius:20px}
.koko-pro-prow{display:flex;align-items:center;gap:9px;padding:7px 0;border-top:1px solid #f6eeed}
.koko-pro-prow:first-of-type{border-top:none}
.koko-pro-flag{font-size:15px;line-height:1;width:20px;text-align:center}
.koko-pro-pname{font-size:13px;color:#4a3a3b;flex:1}
.koko-pro-pbar{height:7px;border-radius:4px;background:linear-gradient(90deg,#9aa4ae,#c6cdd4)}
.koko-pro-pnum{font-size:12.5px;font-weight:700;color:#6a747e;font-variant-numeric:tabular-nums;width:34px;text-align:right}
.koko-pro-blur{filter:blur(4px);opacity:.85;user-select:none;pointer-events:none}
.koko-pro-cta{display:flex;flex-direction:column;align-items:flex-start;gap:12px}
.koko-pro-price{font-size:14px;color:#42566b}
.koko-pro-price b{font-size:20px;font-weight:800;color:#2a1416;letter-spacing:-.01em}
.koko-pro-price .koko-pro-per{font-size:13px;color:#6a7d90;font-weight:500}
.koko-pro-btn{display:inline-flex;align-items:center;gap:9px;background:#025fb6;color:#fff !important;text-decoration: none!important;font-size:15px;font-weight:700;padding:12px 22px;border-radius:7px;border:1px solid #024f97;box-shadow:0 1px 2px rgba(2,79,151,.25);text-decoration:none}
.koko-pro-btn:hover,.koko-pro-btn:focus{background:#024f97;color:#fff}
.koko-pro-link{font-size:13px;font-weight:600;color:#025fb6 !important;text-decoration:none!important;}
.koko-pro-link:hover{text-decoration:underline}
.koko-pro-proof{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#5a6e82;margin-top:2px}
.koko-pro-stars{color:#e8a53d;font-size:13px;letter-spacing:1px}
.koko-pro-reassure{display:flex;align-items:center;gap:7px;font-size:12px;color:#6a7d90;margin-top:14px}
.koko-pro-reassure svg{flex:none}
.koko-pro-lead,.koko-pro-cta,.koko-pro-peek{min-width:0}
@container kokopro (max-width:1040px){.koko-pro-banner{grid-template-columns:1.4fr 280px}.koko-pro-peek{display:none}}
@container kokopro (max-width:640px){.koko-pro-banner{grid-template-columns:1fr;gap:22px;padding:24px}.koko-pro-h{font-size:21px;margin-top:12px}.koko-pro-cta{align-items:stretch}.koko-pro-btn{width:100%;justify-content:center}}
</style>

<div class="koko-pro-wrap">
    <div class="koko-pro-banner">
        <div class="koko-pro-lead">
            <div class="koko-pro-brandrow"><span class="koko-pro-logo"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="32" fill="#b60205"/><path d="M48.42 20.641a.6.6 0 0 0-.66.15L31.984 37.736l-7.36-7.36a.6.6 0 0 0-.848 0l-8.4 8.4a.6.6 0 0 0-.176.424v3.6a.6.6 0 0 0 .6.6h32.4a.6.6 0 0 0 .6-.6V21.2a.6.6 0 0 0-.38-.559z" fill="#fff"/></svg></span><span class="koko-pro-badge"><?php esc_html_e('Koko Analytics', 'koko-analytics'); ?> <b><?php esc_html_e('Pro', 'koko-analytics'); ?></b></span></div>
            <div class="koko-pro-h"><?php esc_html_e("You're seeing half the story.", 'koko-analytics'); ?> <em><?php esc_html_e('Unlock the other half.', 'koko-analytics'); ?></em></div>
            <p class="koko-pro-sub">
                <?php
                printf(
                    /* translators: %s: number of visitors */
                    wp_kses_post(__('Your dashboard shows <b>%s visitors</b> this period, but not <b>where</b> they\'re from or <b>what</b> they used. Koko Analytics Pro adds geolocation, device stats, UTM campaigns and scheduled email reports.', 'koko-analytics')),
                    esc_html(number_format_i18n($koko_pro_visitors))
                );
                ?>
            </p>
            <div class="koko-pro-chips">
                <span class="koko-pro-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#025fb6" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/></svg><?php esc_html_e('Geolocation', 'koko-analytics'); ?></span>
                <span class="koko-pro-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#025fb6" stroke-width="2"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/></svg><?php esc_html_e('Device stats', 'koko-analytics'); ?></span>
                <span class="koko-pro-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#025fb6" stroke-width="2"><path d="M4 12l4-4m-4 4l4 4m-4-4h11a5 5 0 015 5v1"/></svg><?php esc_html_e('UTM tracking', 'koko-analytics'); ?></span>
                <span class="koko-pro-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#025fb6" stroke-width="2"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h9"/></svg><?php esc_html_e('Event tracking', 'koko-analytics'); ?></span>
                <span class="koko-pro-chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#025fb6" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 7l8 6 8-6"/></svg><?php esc_html_e('Email reports', 'koko-analytics'); ?></span>
            </div>
            <div class="koko-pro-reassure"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5a8a5a" stroke-width="2"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/></svg><?php esc_html_e('Still 100% cookieless. Still no third-party services. Your data stays on your server.', 'koko-analytics'); ?></div>
        </div>
        <div class="koko-pro-peek">
            <div class="koko-pro-peek-h"><span><?php esc_html_e('Top countries', 'koko-analytics'); ?></span><span class="koko-pro-lock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#8a949e" stroke-width="2.5"><rect x="5" y="11" width="14" height="9" rx="1.5"/><path d="M8 11V8a4 4 0 018 0v3"/></svg><?php esc_html_e('Pro', 'koko-analytics'); ?></span></div>
            <div class="koko-pro-blur" aria-hidden="true">
                <div class="koko-pro-prow"><span class="koko-pro-flag">🇺🇸</span><span class="koko-pro-pname">United States</span><span class="koko-pro-pbar" style="width:78px"></span><span class="koko-pro-pnum">103</span></div>
                <div class="koko-pro-prow"><span class="koko-pro-flag">🇩🇪</span><span class="koko-pro-pname">Germany</span><span class="koko-pro-pbar" style="width:61px"></span><span class="koko-pro-pnum">81</span></div>
                <div class="koko-pro-prow"><span class="koko-pro-flag">🇳🇱</span><span class="koko-pro-pname">Netherlands</span><span class="koko-pro-pbar" style="width:54px"></span><span class="koko-pro-pnum">71</span></div>
                <div class="koko-pro-prow"><span class="koko-pro-flag">🇬🇧</span><span class="koko-pro-pname">United Kingdom</span><span class="koko-pro-pbar" style="width:37px"></span><span class="koko-pro-pnum">49</span></div>
            </div>
        </div>
        <div class="koko-pro-cta">
            <a class="koko-pro-btn" href="<?php echo esc_url($koko_pro_price_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Upgrade to Pro', 'koko-analytics'); ?><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4"><path d="M5 12h13M13 6l6 6-6 6"/></svg></a>
            <a class="koko-pro-link" href="<?php echo esc_url($koko_pro_more_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('See everything in Pro →', 'koko-analytics'); ?></a>
            <div class="koko-pro-proof"><span class="koko-pro-stars">★★★★★</span><?php esc_html_e('Trusted by thousands of site owners', 'koko-analytics'); ?></div>
        </div>
    </div>
</div>
