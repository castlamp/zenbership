<?php

/**
 * GRAPH RENDERING
 *
 * Works directly in with stats compiled through
 * the stats class.
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */
class graph
{

    protected $options;

    protected $title;

    protected $div_id;

    protected $plot_data;

    protected $stat_data;

    protected $categories;

    protected $series;

    public $final;

    /**
     * @param array $stat_data Array of options for the graph
     *                         Built around stats class.
     *                         key
     *                         start_date
     *                         end_date
     *                         type
     *                         increments

     */
    function __construct($stat_data, $options)
    {
        $this->stat_data = $stat_data;
        $this->options   = $options;
        $this->build_options();
        $this->build_stat_data();
        $this->generate_graph();

    }

    /**
     * Return the final graph

     */
    function __toString()
    {
        return (string)$this->final;

    }

    function build_stat_data()
    {
        $cur          = -1;
        $this->series = "series: [\n";
        foreach ($this->stat_data as $series) {
            $cur++;
            if ($cur == 0) {
                $series['color'] = '#92D24B';

            } else if ($cur == 1) {
                $series['color'] = '#5298E0';

            } else if ($cur == 2) {
                $series['color'] = '#E05151';

            } else if ($cur == 3) {
                $series['color'] = '#9933FF';

            } else if ($cur == 4) {
                $series['color'] = '#FFFF00';

            }
            if (empty($this->options['end_date'])) {
                $this->options['end_date'] = current_date();

            }
            if (!empty($this->options['start_date']) && !empty($this->options['end_date'])) {
                $this->options['increments'] = ceil(strtotime($this->options['end_date']) - strtotime($this->options['start_date']));

            }
            if (empty($this->options['increments'])) {
                $this->options['increments'] = '7';

            }
            $inc        = $this->options['increments'];
            $start_time = strtotime($this->options['end_date']);
            if ($this->options['type'] == 'year') {
                $use_type  = 'year';
                $dif_maker = 31536000;

            } else if ($this->options['type'] == 'month') {
                $use_type  = 'month';
                $dif_maker = 2628000;

            } else if ($this->options['type'] == 'hour') {
                $use_type  = 'hour';
                $dif_maker = 3600;

            } else {
                $use_type  = 'day';
                $dif_maker = 86400; // Day
            }
            $cates = array();
            $plots = array();
            while ($inc > 0) {
                if ($use_type == 'year') {
                    $key       = $series['key'] . '-' . date('Y', $start_time);
                    $cate_name = date('Y', $start_time);

                } else if ($use_type == 'month') {
                    $key       = $series['key'] . '-' . date('Y-m', $start_time);
                    $cate_name = date('M Y', $start_time);

                } else if ($use_type == 'day') {
                    $key       = $series['key'] . '-' . date('Y-m-d', $start_time);
                    $cate_name = date('M jS', $start_time);

                } else if ($use_type == 'hour') {
                    $key       = $series['key'] . '-' . date('Y-m-d-H', $start_time);
                    $cate_name = date('jS ga', $start_time);

                }

                $data = new stats($key, 'get');

                $data = trim($data);
                if (empty($data)) { $data = '0'; }
                if (!empty($series['money']) && $series['money'] == '1') {
                    $mon     = (! empty($data->final)) ? (float)$data->final : 0;
                    $plots[] = number_format($mon, 2);
                } else {
                    $plots[] = $data;
                }
                $cates[] = "'" . $cate_name . "'";
                //$plots .= ',\'' . $data . '\'';
                //$cates .= ',\'' . $cate_name . '\'';
                $inc--;
                $start_time -= $dif_maker;

            }
            $plot_data        = implode(',', array_reverse($plots));
            $this->categories = implode(',', array_reverse($cates));
            $this->series .= "{\n";
            $this->series .= "    name: '" . $series['title'] . "',\n";
            $this->series .= "    data: [" . $plot_data . "],\n";
            $this->series .= "    showInLegend: true,\n";
            $this->series .= "    color: '" . $series['color'] . "'\n";
            if (!empty($this->options['yaxis'])) {
                $this->series .= ",    yAxis: $cur\n";
                if (!empty($this->options['yaxis'][$cur]['type'])) {
                    $this->series .= ", type: '" . $this->options['yaxis'][$cur]['type'] . "'\n";
                }
            }
            $this->series .= "},\n";

        }
        $this->series .= "]\n";

    }

    function build_options()
    {
        if (empty($this->options['color'])) {
            $this->options['color'] = '#92D24B';

        }
        if (empty($this->options['graph_type'])) {
            $this->options['graph_type'] = 'area';

        }
        if (empty($this->options['font'])) {
            $this->options['font'] = 'arial';

        }
        if (empty($this->options['title_size'])) {
            $this->options['title_size'] = '1.1em';

        }
        if (empty($this->options['line_width'])) {
            $this->options['line_width'] = '1';

        }
        if (empty($this->options['tooltip_border'])) {
            $this->options['tooltip_border'] = '1px';

        }
        if (empty($this->options['tooltip_border_radius'])) {
            $this->options['tooltip_border_radius'] = '0';

        }
        if (!empty($this->options['title'])) {
            $this->title = $this->options['title'];

        }
        if (!empty($this->options['element'])) {
            $this->div_id = $this->options['element'];

        } else {
            $this->div_id = 'graph';

        }

    }

    function generate_graph()
    {
        $this->final .= "
            <script type=\"text/javascript\">
            Highcharts.setOptions({
                chart: {
                    style: {
                        fontFamily: '" . $this->options['font'] . "'
                    }
                }
            });
            $(function () {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: '" . $this->div_id . "',
                            type: '" . $this->options['graph_type'] . "'
                        },
                        title: {
                            text: '" . $this->title . "',
                            style: {
                                fontSize: '" . $this->options['title_size'] . "'
                            }
                        },
                        xAxis: {
                            categories: [" . $this->categories . "]
                        },
                        ";
        if (!empty($this->options['yaxis'])) {
            $axise = '';
            $this->final .= "yAxis: [";
            foreach ($this->options['yaxis'] as $axis) {
                $axise .= "
                        {
                            title: {
                                text: '" . $axis['title'] . "'
                            },
                            labels: {
                                enabled: false
                            },
                            plotLines: [{
                                value: 0,
                                width: " . $axis['line_width'] . ",
                            }]
                        },
                ";
            }
            $axise = rtrim($axise, ',');
            $this->final .= $axise;
            $this->final .= "],";
        } else {
            $this->final .= "
                        yAxis: {
                            title: {
                                text: ''
                            },
                            labels: {
                                enabled: false
                            },
                            plotLines: [{
                                value: 0,
                                width: " . $this->options['line_width'] . ",
                                color: '" . $this->options['color'] . "'
                            }]
                        },
            ";
        }
        $this->final .= "
                        tooltip: {
                            formatter: function() {
                                    return this.y;
                            },
                            borderWidth: '" . $this->options['tooltip_border'] . "',
                            borderRadius: '" . $this->options['tooltip_border_radius'] . "',
                            shadow: false
                        },
                        " . $this->series . "
                    });
                });
            });
            </script>
        ";

    }

}



