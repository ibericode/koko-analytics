<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Chart_View
{
    public function __construct(array $data, \DateTimeInterface $dateStart, \DateTimeInterface $dateEnd)
    {
        $tick_width = 100.0 / (float) count($data);
        $y_max = 0;
        foreach ($data as $i => $tick) {
            $y_max = max($y_max, $tick->pageviews);
        }
        $y_max_nice = $this->get_magnitude($y_max);
        $height_modifier = 250 / $y_max_nice;
        $dateFormat = get_option('date_format');
        $margin = 0.1;
        ?>
<div style="position: relative;">
    <svg width="100%" height="280" id="ka-chart-2">
      <g class="axes-y" transform="translate(40, 6)" text-anchor="end">
        <g transform="translate(8, 0)">
            <line stroke="#eee" x1="0" x2="100%" y1="250" y2="250"></line>
            <line stroke="#eee" x1="0" x2="100%" y1="125" y2="125"></line>
            <line stroke="#eee" x1="0" x2="100%" y1="0" y2="0"></line>
        </g>
        <text y="250" fill="#757575" x="0" dy="0.33em">0</text>
        <text y="125" fill="#757575" x="0" dy="0.33em"><?php echo fmt_large_number($y_max_nice / 2); ?></text>
        <text y="0" fill="#757575" x="0" dy="0.33em"><?php echo fmt_large_number($y_max_nice); ?></text>
      </g>
      <g class="axes-x" text-anchor="start" transform="translate(0, 256)">
        <text fill="#757575" x="28" y="10" dy="1em" text-anchor="start"><?php echo $dateStart->format($dateFormat); ?></text>
        <text fill="#757575" x="100%" y="10" dy="1em" text-anchor="end"><?php echo $dateEnd->format($dateFormat); ?></text>
      </g>
       <g class="bars" transform="translate(0, 6)">
        <?php foreach ($data as $i => $tick) {
            echo '<g data-date="', (new \DateTimeImmutable($tick->date))->format($dateFormat), '" data-pageviews="', fmt_large_number($tick->pageviews), '" data-visitors="', fmt_large_number($tick->visitors),'">'; // for hover tooltip
            echo '<rect class="ka--pageviews" height="', $tick->pageviews * $height_modifier,'" y="', 250 - $tick->pageviews * $height_modifier,'"></rect>';
            echo '<rect class="ka--visitors" height="', $tick->visitors * $height_modifier, '" y="', 250 - $tick->visitors * $height_modifier ,'"></rect>';
            echo '<line stroke="#ddd" y1="250" y2="256"></line>';
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
</div>
         <?php
    }

    private function get_magnitude(int $n)
    {
        if ($n < 10) {
            return 10;
        }

        if ($n > 100_000) {
            return ceil($n / 10000.0) * 10000;
        }

        $e = floor(log10($n));
        $pow = pow(10, $e);
        return ceil($n / $pow) * $pow;
    }
}
