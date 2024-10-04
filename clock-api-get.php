<?php

register_rest_route("track-time/v1", "/invoice", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        global $wpdb;
        $invoice = $req->get_param("invoice");
        $result = $wpdb->get_results("SELECT * FROM wp_track_time WHERE invoice = '{$invoice}'", ARRAY_N);
        return [
            "response"	=> $result,
        ];
    }
]);

register_rest_route("track-time/v1", "/employee", [
    "methods"	=> "GET",
    "callback"	=> function(WP_REST_Request $req){
        global $wpdb;
        $date		= $req->get_param("date");
        $whois		= $req->get_param("whois");
        $result 	= $wpdb->get_results("SELECT * FROM wp_track_time WHERE employee = {$whois} and DATE(date) = '{$date}'", ARRAY_N);
        return [
            "response"	=> $result,
        ];
    }
]);