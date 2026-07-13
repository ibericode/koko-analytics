<?php

if (! defined('ABSPATH')) {
    exit;
}

// Fallbacks so the file also renders standalone.
$koko_pro_visitors  = isset($totals) && is_object($totals) ? (int) $totals->visitors : 0;
$koko_pro_price_url = 'https://www.kokoanalytics.com/pricing/?utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-dashboard-upgrade';
$koko_pro_more_url  = 'https://www.kokoanalytics.com/features/?utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-dashboard-upgrade#pro-features';
?>
<style>
.ka-upsell-banner * {box-sizing: border-box; }
.ka-upsell-banner{position:relative;display:grid;grid-template-columns:1.1fr 1fr;gap:40px;align-items:center;background:linear-gradient(105deg,#f7fbfb 0%,#f1f7f7 55%,#eaf4f4 100%);border:1px solid #dbe8e8;border-radius:10px;padding:30px 34px;overflow:hidden}
.ka-upsell-brandrow{display:flex;align-items:center;gap:9px}
.ka-upsell-h{font-size:23px;line-height:1.2;font-weight:700;letter-spacing:-.01em;margin: 16px 0 0;color:#021630;text-wrap:balance; max-width: 70ch;}
.ka-upsell-sub{font-size:14px;line-height:1.5;color:#48595f;max-width:70ch; margin:16px 0 0;}
.ka-upsell-peek{position:relative;background:#fff;border:1px solid #d7e6e6;border-radius:9px;padding:13px 15px 15px;box-shadow:0 1px 2px rgba(2,22,48,.05)}
.ka-upsell-peek-h{display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#8598a0;margin-bottom:10px}
.ka-upsell-lock-tag{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;letter-spacing:.04em;color:#6a747e;background:#eef1f4;border:1px solid #dde3e9;padding:3px 8px;border-radius:20px;text-transform:none}
.ka-upsell-prow{display:flex;align-items:center;gap:9px;padding:7px 0;border-top:1px solid #eaf3f3}
.ka-upsell-prow:first-of-type{border-top:none}
.ka-upsell-flag{font-size:15px;line-height:1;width:20px;text-align:center}
.ka-upsell-pname{font-size:13px;color:#33454c;flex:1}
.ka-upsell-pbar{height:7px;border-radius:4px;background:linear-gradient(90deg,#9aa4ae,#c6cdd4)}
.ka-upsell-pnum{font-size:13px;font-weight:700;color:#6a747e;font-variant-numeric:tabular-nums;width:34px;text-align:right}
.ka-upsell-blurred{filter:blur(4px);opacity:.85;user-select:none;pointer-events:none}
.ka-upsell-cta{display:flex;flex-wrap:wrap;align-items:center;gap:18px;margin:16px 0 0;}
.ka-upsell-btn{display:inline-flex;align-items:center;gap:9px;background:#021630;color:#fff !important;text-decoration: none !important;font-size:15px;font-weight:700;padding:12px 22px;border-radius:7px;border:1px solid #000c1c;box-shadow:0 1px 2px rgba(2,22,48,.3);cursor:pointer;text-decoration:none}
.ka-upsell-link{font-size:13px;font-weight:600;color:#069494 !important;cursor:pointer}
.ka-upsell-link:hover{text-decoration:underline}
.ka-upsell-reassure{display:flex;align-items:center;gap:7px;font-size:13px;color:#6a7d90;margin:16px 0 0;}
.ka-upsell-reassure svg{flex:none}
.ka-upsell-lead,.ka-upsell-cta,.ka-upsell-peek{min-width:0}
@media(max-width:820px){.ka-upsell-banner{grid-template-columns:1fr}.ka-upsell-peek{display:none}}
@media(max-width:520px){.ka-upsell-banner{gap:22px;padding:24px}.ka-upsell-h{font-size:21px;margin-top:12px}.ka-upsell-cta{flex-direction:column;align-items:stretch}.ka-upsell-btn{width:100%;justify-content:center}}
</style>
<div class="ka-upsell-banner">
<div class="ka-upsell-lead">
<div class="ka-upsell-brandrow"><img class="ka-upsell-logo" src="<?php echo esc_url(plugins_url('/assets/img/koko-analytics-logo.png', KOKO_ANALYTICS_PLUGIN_FILE)); ?>" alt="Koko Analytics" height="25" width="201"></div>
<div class="ka-upsell-h">You're seeing half the story. Unlock the other half.</div>
<div class="ka-upsell-sub">Your dashboard shows <b><?php echo (int) $koko_pro_visitors; ?> visitors</b> this period, but not <b>where</b> they came from or <b>what</b> they used. Koko Analytics Pro unlocks geolocation, device statistics, UTM campaign tracking and scheduled email reports.</div>
<div class="ka-upsell-reassure">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#5a8a5a" stroke-width="2"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"></path><path d="M9 12l2 2 4-4"></path></svg>
    Still 100% privacy friendly. Still 100% first party.
</div>
<div class="ka-upsell-cta">
<a class="ka-upsell-btn" href="<?php echo esc_url($koko_pro_price_url); ?>" target="_blank">Upgrade to Koko Analytics Pro<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4"><path d="M5 12h13M13 6l6 6-6 6"></path></svg></a>
<a class="ka-upsell-link" href="<?php echo esc_url($koko_pro_more_url); ?>" target="_blank">View Pro features</a>
</div>
</div>
<div class="ka-upsell-peek">
<div class="ka-upsell-peek-h"><span>Top countries</span><span class="ka-upsell-lock-tag"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#8a949e" stroke-width="2.5"><rect x="5" y="11" width="14" height="9" rx="1.5"></rect><path d="M8 11V8a4 4 0 018 0v3"></path></svg>Koko Analytics Pro</span></div>
<div class="ka-upsell-blurred">
<div class="ka-upsell-prow"><span class="ka-upsell-flag">🇺🇸</span><span class="ka-upsell-pname">United States</span><span class="ka-upsell-pbar" style="width:78px"></span><span class="ka-upsell-pnum">103</span></div>
<div class="ka-upsell-prow"><span class="ka-upsell-flag">🇩🇪</span><span class="ka-upsell-pname">Germany</span><span class="ka-upsell-pbar" style="width:61px"></span><span class="ka-upsell-pnum">81</span></div>
<div class="ka-upsell-prow"><span class="ka-upsell-flag">🇳🇱</span><span class="ka-upsell-pname">Netherlands</span><span class="ka-upsell-pbar" style="width:54px"></span><span class="ka-upsell-pnum">71</span></div>
<div class="ka-upsell-prow"><span class="ka-upsell-flag">🇬🇧</span><span class="ka-upsell-pname">United Kingdom</span><span class="ka-upsell-pbar" style="width:37px"></span><span class="ka-upsell-pnum">49</span></div>
</div>
</div>
</div>
