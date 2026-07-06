<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Chart_View
{
    public function __construct(array $data, \DateTimeInterface $date_start, \DateTimeInterface $date_end, int $height = 280, bool $show_head = true, string $group = 'day')
    {
        $y_max           = array_reduce($data, function ($carry, $tick) {
            return max($carry, $tick->pageviews);
        }, 0);
        $y_max_nice      = $this->get_magnitude($y_max);
        $padding_top     = 6;
        $padding_bottom  = 24;
        $padding_left    = 4 + strlen(number_format_i18n($y_max_nice)) * 8;
        $inner_height    = $height - $padding_top - $padding_bottom;
        $height_modifier = $y_max_nice > 0 ? $inner_height / $y_max_nice : 1;
        $date_format     = (string) get_option('date_format', 'Y-m-d');
        $days_diff       = abs($date_end->diff($date_start)->days);
        $timezone        = wp_timezone();
        $group_options = array_filter($this->get_grouping_options(), function ($option) use ($days_diff) {
            return $days_diff > $option['min'];
        });
        $adjective = $group_options[$group]['adjective'] ?? $group_options['day']['adjective'];
        ?>
        
        <?php if ($show_head) : ?>
        <div class="ka-card-head">
            <div>
                <div class="ka-card-head-title"><?php esc_html_e('Visitors & pageviews', 'koko-analytics'); ?></div>
                <div class="ka-card-head-desc"><?php printf(esc_html__('%1$s totals over the selected period', 'koko-analytics'), esc_html($adjective)); ?></div>
            </div>
            <div class="ka-chart-options">
                <div class="ka-chart-legend">
                    <span><i class="ka-chart-legend-item ka-chart-legend-item-visitors"></i> <?php esc_html_e('Visitors', 'koko-analytics'); ?></span>
                    <span><i class="ka-chart-legend-item ka-chart-legend-item-pageviews"></i> <?php esc_html_e('Pageviews', 'koko-analytics'); ?></span>
                </div>
                <div class="ka-chart-group">
                    <?php foreach ($group_options as $key => $option) : ?>
                        <a href="<?= esc_attr(add_query_arg(['group' => $key])); ?>" class="<?php echo $group === $key ? 'on' : ''; ?>" rel="nofollow"><?php echo esc_html($option['label']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; // end if show head ?>
        <div class="ka-chart">
            <svg width="100%" height="<?= esc_attr((string) $height); ?>" id="ka-chart">
                <g class="axes-y" transform="translate(<?= esc_attr((string) $padding_left); ?>, <?= esc_attr((string) $padding_top); ?>)" text-anchor="end" data-padding="<?= esc_attr((string) $padding_left); ?>">
                <text x="0" y="<?= esc_attr((string) $inner_height); ?>" fill="#757575" dy="0.3em" >0</text>
                <text x="0" y="<?= esc_attr((string) ($inner_height / 2)); ?>" fill="#757575" dy="0.3em"><?= esc_html(\number_format_i18n($y_max_nice / 2)); ?></text>
                <text x="0" y="0" fill="#757575" dy="0.3em"><?= esc_html(\number_format_i18n($y_max_nice)); ?></text>
                <line stroke="#eee" x1="8" x2="100%" y1="<?= esc_attr((string) $inner_height); ?>" y2="<?= esc_attr((string) $inner_height); ?>"></line>
                <line stroke="#eee" x1="8" x2="100%" y1="<?= esc_attr((string) ($inner_height / 2)); ?>" y2="<?= esc_attr((string) ($inner_height / 2)); ?>"></line>
                <line stroke="#eee" x1="8" x2="100%" y1="0" y2="0"></line>
                </g>
                <g class="axes-x" text-anchor="start" transform="translate(0, <?= esc_attr((string) ($inner_height + 4)); ?>)">
                <text fill="#757575" x="<?= esc_attr((string) $padding_left); ?>" y="10" dy="1em" text-anchor="start"><?= esc_html(\wp_date($date_format, $date_start->getTimestamp())); ?></text>
                <text fill="#757575" x="100%" y="10" dy="1em" text-anchor="end"><?= esc_html(\wp_date($date_format, $date_end->getTimestamp())); ?></text>
                </g>
                <g class="bars" transform="translate(0, <?= esc_attr((string) $padding_top); ?>)">
                <?php
                foreach ($data as $tick) {
                    $dt         = (new \DateTimeImmutable($tick->date, $timezone));
                    $is_weekend = (int) $dt->format('N') >= 6;
                    $tick_label = $this->format_tick_date($dt, $group, $date_format);
                    // data attributes are for the hover tooltip, which is handled in JS
                    echo '<g ', $is_weekend ? 'class="weekend" ' : '', 'data-date="', esc_attr($tick_label), '" data-pageviews="', esc_attr(\number_format_i18n($tick->pageviews)), '" data-visitors="', esc_attr(\number_format_i18n($tick->visitors)), '">';
                    echo '<rect class="ka--pageviews" width="0" height="', esc_attr((string) ($tick->pageviews * $height_modifier)), '" y="', esc_attr((string) ($inner_height - $tick->pageviews * $height_modifier)), '"></rect>';
                    echo '<rect class="ka--visitors" width="0" height="', esc_attr((string) ($tick->visitors * $height_modifier)), '" y="', esc_attr((string) ($inner_height - $tick->visitors * $height_modifier)), '"></rect>';
                    echo '<line stroke="#ddd" y1="', esc_attr((string) $inner_height), '" y2="', esc_attr((string) ($inner_height + 6)), '"></line>';
                    echo '</g>';
                }
                ?>
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

    protected function get_grouping_options(): array
    {
        return [
            'day' => [
                'min' => 0,
                'label' => esc_html__('Days', 'koko-analytics'),
                'adjective' => esc_html__('Daily', 'koko-analytics'),
            ],
            'week' => [
                'min' => 7,
                'label' => esc_html__('Weeks', 'koko-analytics'),
                'adjective' => esc_html__('Weekly', 'koko-analytics'),
            ],
            'month' => [
                'min' => 31,
                'label' => esc_html__('Months', 'koko-analytics'),
                'adjective' => esc_html__('Monthly', 'koko-analytics'),
            ],
            'year' => [
                'min' => 365,
                'label' => esc_html__('Years', 'koko-analytics'),
                'adjective' => esc_html__('Yearly', 'koko-analytics'),
            ]
        ];
    }

    protected function format_tick_date(\DateTimeImmutable $dt, string $group, string $date_format): string
    {
        switch ($group) {
            case 'week':
                $week_end = $dt->modify('+6 days');
                return \wp_date('M j', $dt->getTimestamp()) . ' – ' . \wp_date('M j, Y', $week_end->getTimestamp());
            case 'month':
                return \wp_date('F Y', $dt->getTimestamp());
            case 'year':
                return \wp_date('Y', $dt->getTimestamp());
            default:
                return \wp_date($date_format, $dt->getTimestamp());
        }
    }

    protected function get_magnitude(int $n): int
    {
        if ($n < 10) {
            return 10;
        }

        if ($n > 100000) {
            return (int) ceil($n / 10000.0) * 10000;
        }

        $e   = floor(log10($n));
        $pow = pow(10, $e);
        return (int) (ceil($n / $pow) * $pow);
    }
}
