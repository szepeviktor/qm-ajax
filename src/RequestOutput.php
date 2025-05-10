<?php

declare(strict_types=1);

namespace SzepeViktor\WordPress\QueryMonitor\Ajax;

use QM_Collector;

class RequestOutput extends \QM_Output_Html
{
    public function __construct(QM_Collector $collector)
    {
        parent::__construct($collector);
        add_filter('qm/output/menus', [$this, 'admin_menu'], 51, 1);
    }

    public function name(): string
    {
        return 'Request Variables';
    }

    public function output(): void
    {
        /** @var array<string, string> $data */
        $data = $this->collector->get_data()->request_variables;

        $this->before_tabular_output();

        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Name</th>';
        echo '<th scope="col">Value</th>';
        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        foreach ($data as $name => $value) {
            echo '<tr>';
            echo '<th scope="row"><code>';
            echo esc_html($name);
            echo '<code></th>';
            echo '<td>';
            echo esc_html($value);
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';

        $this->after_tabular_output();
    }
}
