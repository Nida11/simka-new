<?php
if (!function_exists('generate_pagination')) {
    function generate_pagination($base_url, $total_rows, $limit_per_page, $current_page = 1) {
        $total_pages = ceil($total_rows / $limit_per_page);
        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Tombol Prev
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '/' . $prev_page . '">&laquo; Prev</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>';
        }

        // Nomor halaman
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '/' . $i . '">' . $i . '</a></li>';
            }
        }

        // Tombol Next
        if ($current_page < $total_pages) {
            $next_page = $current_page + 1;
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '/' . $next_page . '">Next &raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }
}
