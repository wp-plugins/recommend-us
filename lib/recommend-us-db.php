<?php
/*
 * Database functions for Recommend Us
 */

class RecommendUsDB extends MSBDDB {

    var $parent;
    var $debug_queries = FALSE;

    function __construct($parent) {
        $this->parent = $parent;
        $this->sqltable = $this->parent->sqltable;
        parent::__construct();
    }

    function create_update_database() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = "CREATE TABLE $this->sqltable (
                 id int(11) NOT NULL AUTO_INCREMENT,
                 date_time datetime NOT NULL,
                 recommend_by_name varchar(100) DEFAULT NULL,
                 recommend_by_email varchar(150) DEFAULT NULL,
                 recommendation_title varchar(100) DEFAULT NULL,
                 rating_number tinyint(2) DEFAULT '0',
                 recommendation_text text,
                 recommendation_status tinyint(1) DEFAULT '0',
                 recommend_by_ip varchar(15) DEFAULT NULL,
                PRIMARY KEY (id)
                )
                CHARACTER SET utf8
                COLLATE utf8_general_ci;";
        dbDelta($sql);
    }

    function pending_reviews_count() {
        $this->select('COUNT(*)');
        $this->where('recommendation_status', 0);
        return $this->get_var();
    }

    function approved_reviews_count() {
        $this->select('COUNT(*)');
        $this->where('recommendation_status', 1);
        return $this->get_var();
    }

}
