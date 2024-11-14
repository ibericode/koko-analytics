<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Chart_View
{
    public function __construct(array $data, \DateTimeInterface $dateStart, \DateTimeInterface $dateEnd, int $height = 280)
    {
        $tick_width = count($data) > 0 ? 100.0 / count($data) : 100.0;
        $y_max = 0;
        foreach ($data as $i => $tick) {
            $y_max = max($y_max, $tick->pageviews);
        }
        $y_max_nice = $this->get_magnitude($y_max);
        $padding_top = 6;
        $padding_bottom = 24;
        $padding_left = 4 + max(5, strlen(number_format_i18n($y_max_nice)) * 8);
        $inner_height = $height - $padding_top - $padding_bottom;
        $height_modifier = $y_max_nice > 0 ? $inner_height / $y_max_nice : 1;
        $dateFormat = (string) get_option('date_format', 'Y-m-d');
        $margin = 0.1;
        ?>
        <div class="ka-chart">
            <svg width="100%" height="<?php echo $height; ?>" id="ka-chart">
              <g class="axes-y" transform="translate(<?php echo $padding_left; ?>, <?php echo $padding_top; ?>)" text-anchor="end" data-padding="<?php echo $padding_left; ?>">
                <text x="0" y="<?php echo $inner_height; ?>" fill="#757575" dy="0.3em" >0</text>
                <text x="0" y="<?php echo $inner_height / 2; ?>" fill="#757575" dy="0.3em"><?php echo number_format_i18n($y_max_nice / 2); ?></text>
                <text x="0" y="0" fill="#757575" dy="0.3em"><?php echo number_format_i18n($y_max_nice); ?></text>
                <line stroke="#eee" x1="8" x2="100%" y1="<?php echo $inner_height; ?>" y2="<?php echo $inner_height; ?>"></line>
                <line stroke="#eee" x1="8" x2="100%" y1="<?php echo $inner_height / 2; ?>" y2="<?php echo $inner_height / 2; ?>"></line>
                <line stroke="#eee" x1="8" x2="100%" y1="0" y2="0"></line>
              </g>
              <g class="axes-x" text-anchor="start" transform="translate(0, <?php echo $inner_height + 4; ?>)">
                <text fill="#757575" x="<?php echo $padding_left; ?>" y="10" dy="1em" text-anchor="start"><?php echo $dateStart->format($dateFormat); ?></text>
                <text fill="#757575" x="100%" y="10" dy="1em" text-anchor="end"><?php echo $dateEnd->format($dateFormat); ?></text>
              </g>
               <g class="bars" transform="translate(0, <?php echo $padding_top; ?>)" style="display: none;">
                <?php foreach ($data as $tick) {
                    $dt = (new \DateTimeImmutable($tick->date));
                    $is_weekend = (int) $dt->format('N') >= 6;
                    $class_attr = $is_weekend ? 'class="weekend" ' : '';
                    echo '<g ', $class_attr, 'data-date="', $dt->format($dateFormat), '" data-pageviews="', \number_format_i18n($tick->pageviews), '" data-visitors="', \number_format_i18n($tick->visitors),'">'; // for hover tooltip
                    echo '<rect class="ka--pageviews" height="', $tick->pageviews * $height_modifier,'" y="', $inner_height - $tick->pageviews * $height_modifier,'"></rect>';
                    echo '<rect class="ka--visitors" height="', $tick->visitors * $height_modifier, '" y="', $inner_height - $tick->visitors * $height_modifier ,'"></rect>';
                    echo '<line stroke="#ddd" y1="', $inner_height, '" y2="', $inner_height + 6,'"></line>';
                    echo '</g>';
                } ?>
               </g>
            </svg>
            <div class="ka-chart--tooltip" style="display: none;">
                <div class="ka-chart--tooltip-box">
                  <div class="ka-chart--tooltip-heading"></div>
                  <div style="display: flex">
                    <div class="ka-chart--tooltip-content ka--visitors">
                      <div class="ka-chart--tooltip-amount"></div>
                      <div><?php esc_html_e('Visitors', 'koko-analytics'); ?></div>
                    </div>
                    <div class="ka-chart--tooltip-content ka--pageviews">
                      <div class="ka-chart--tooltip-amount"></div>
                      <div><?php esc_html_e('Pageviews', 'koko-analytics'); ?></div>
                    </div>
                  </div>
                </div>
                <div class="ka-chart--tooltip-arrow"></div>
            </div>
        </div><?php
    }

    private function get_magnitude(int $n): int
    {
        if ($n < 10) {
            return 10;
        }

        if ($n > 100000) {
            return (int) ceil($n / 10000.0) * 10000;
        }

        $e = floor(log10($n));
        $pow = pow(10, $e);
        return (int) ceil($n / $pow) * $pow;
    }
}
